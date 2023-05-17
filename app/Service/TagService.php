<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Service;

use App\Model\ImageGroup;
use App\Model\MemberTag;
use App\Model\Tag;
use App\Model\TagCorrespond;
use App\Model\TagHasGroup;
use App\Model\TagPopular;
use App\Model\Video;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

use function Hyperf\Support\env;

class TagService
{
    public const POPULAR_TAG_CACHE_KEY = 'popular_tag';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function getTags(): Collection
    {
        $list = Tag::all();
        foreach ($list as $key => $value) {
            if (! empty($value->img)) {
                $list[$key]->img = env('VIDEO_THUMB_URL') . $value->img;
            }
        }
        return $list;
    }

    public function createTag(array $params): void
    {
        $model = new Tag();
        if (! empty($params['id'])) {
            $model = Tag::find($params['id']);
        }
        if (! empty($params['image_url'])) {
            $model->img = $params['image_url'];
            // $model->height = $data['height'];
            // $model->weight = $data['weight'];
        }
        $model->name = $params['name'];
        $model->user_id = $params['user_id'];
        $model->hot_order = $params['hot_order'];
        $model->save();

        if (count($params['groups']) > 0) {
            $id = $model->id;
            $this->createTagGroupRelationship($params['groups'], $id);
        }

        $this->redis->del(self::POPULAR_TAG_CACHE_KEY);
        $this->getPopularTag();
    }

    // 影片TAG關聯
    public function videoCorrespondTag(array $data, int $videoId)
    {
        if ($data['tags'] != '') {
            $tags = explode(',', $data['tags']);
            foreach ($tags as $v) {
                if (strlen($v) > 1) {
                    $tag = self::createTagByName($v, 1);
                    self::createTagRelationship(Video::class, $videoId, $tag->id);
                }
            }
        }
    }

    // 新增時以name為主 有重覆不新增
    public function createTagByName(string $name, int $userId)
    {
        if (Tag::where('name', $name)->exists()) {
            $model = Tag::where('name', $name)->first();
        } else {
            $model = new Tag();
        }
        $model->name = $name;
        $model->user_id = $userId;
        $model->save();
        return $model;
    }

    public function createTagRelationship(string $className, int $classId, int $tagId): void
    {
        $model = new TagCorrespond();
        $model->correspond_type = $className;
        $model->correspond_id = $classId;
        $model->tag_id = $tagId;
        $model->save();
    }

    public function createTagRelationshipArr(string $className, int $classId, array $tagIds): void
    {
        TagCorrespond::where('correspond_type', $className)
            ->where('correspond_id', $classId)
            ->delete();
        foreach ($tagIds as $tagId) {
            $this->createTagRelationship($className, $classId, (int) $tagId);
        }
    }

    public function getPopularTag()
    {
        if ($this->redis->exists(self::POPULAR_TAG_CACHE_KEY)) {
            return json_decode($this->redis->get(self::POPULAR_TAG_CACHE_KEY), true);
        }

        $tags = Tag::where('hot_order', '>=', 1)
            ->orderBy('hot_order')
            ->get();

        $collect = $this->calculatePopularTag($tags->pluck('id')->toArray());

        $result = $this->generatePopularTags($tags->toArray(), $collect->toArray());

        if (! empty($result)) {
            $this->redis->set(self::POPULAR_TAG_CACHE_KEY, json_encode($result));
        }

        return $result;
    }

    public function calculatePopularTag(array $hotTagIds)
    {
        return MemberTag::groupBy('tag_id')
            ->select('tag_id', Db::raw('sum(count) as total'), 'tags.name')
            ->join('tags', 'tags.id', '=', 'member_tags.tag_id')
            ->orderByDesc('total')
            ->whereNotIn('tag_id', $hotTagIds)
            ->limit(10)
            ->get();
    }

    public static function tagIdsToInt(?array $tags): array
    {
        $result = [];

        if (empty($tags)) {
            return $result;
        }

        foreach ($tags as $tag) {
            $result[] = (int) $tag;
        }

        return $result;
    }

    // 新增或更新標籤群組關係
    public function createTagGroupRelationship(array $groups, int $tag_id)
    {
        TagHasGroup::where('tag_id', $tag_id)->delete();
        foreach ($groups as $key => $value) {
            $model = TagHasGroup::where('tag_group_id', $value)
                ->where('tag_id', $tag_id);
            if (! $model->exists()) {
                $model = new TagHasGroup();
                $model->tag_id = $tag_id;
                $model->tag_group_id = $value;
                $model->save();
            }
        }
    }

    public function getTagsByModelType(?string $type, ?int $id): array
    {
        if (empty($type) or empty($id)) {
            return [];
        }

        return match ($type) {
            'image_group' => ImageGroup::find($id)->tags()->pluck('tags.id')->toArray() ?? [],
            default => Video::find($id)->tags()->pluck('tags.id')->toArray() ?? [],
        };
    }

    public function getTypeIdsByTagIds(array $tagIds, string $type, int $page, int $limit): array
    {
        return TagCorrespond::where('correspond_type', $type)
            ->whereIn('tag_id', $tagIds)
            ->offset($page * $limit)
            ->limit($limit)
            ->get()
            ->pluck('correspond_id')
            ->toArray();
    }

    protected function generatePopularTags(array $tags, array $popularTag)
    {
        $result = [];
        foreach ($tags as $tag) {
            $result[] = [
                'tag_id' => $tag['id'],
                'count' => PHP_INT_MAX,
                'name' => $tag['name'],
            ];
        }

        return array_merge($result, $popularTag);
    }

    // 獲取標籤詳細資料
    public function getTagDetail(int $tag_id)
    {
        // 撈取標籤基礎資料
        $tag = Tag::select('id', 'name', 'img')->where('id', $tag_id)->first()->toArray();
        if(!empty($tag['img'])){
            $tag['img'] = env('VIDEO_THUMB_URL') . $tag['img'];
        }
        // 撈取影片作品數
        $video_num = TagCorrespond::where('tag_id', $tag_id)->where('tag_corresponds.correspond_type', Video::class)->get()->count();
        // 撈取套圖作品數
        $image_num = TagCorrespond::where('tag_id', $tag_id)->where('tag_corresponds.correspond_type', ImageGroup::class)->get()->count();
        // 撈取熱門標籤
        $popular_tags = TagPopular::selectRaw('popular_tag_id as tag_id, popular_tag_name as tag_name')->where('tag_id', $tag_id)->get()->toArray();
        foreach ($popular_tags as $key => $popular_tag) {
            if(trim($popular_tag['tag_name']) == trim($tag['name'])){
                $popular_tags[$key]['tag_name'] = trans('api.tag_control.all');
            }
        }
        $result['tag'] = $tag;
        $result['video_num'] = $video_num;
        $result['image_num'] = $image_num;
        $result['popular_tags'] = $popular_tags;
        return $result;
    }

    // 計算各標籤下的作品集內的top6標籤
    public function calculateTop6Tag()
    {
        // 清空 tag_populars
        TagPopular::truncate();

        // 獲取所有標籤id
        $tags = Tag::select('id', 'name')->get();

        // 計算各標籤下的作品集內的top6標籤
        foreach ($tags as $key => $tag) {
            // video
            $video_ids = TagCorrespond::where('correspond_type', Video::class)
                    ->where('tag_id', $tag->id)->pluck('correspond_id')->toArray();
            // 撈出該影片下所有標籤id與次數
            $video_all_ids = TagCorrespond::selectRaw('tag_corresponds.tag_id, tags.name, count(*) as count')
                    ->join('tags', 'tags.id', 'tag_corresponds.tag_id')
                    ->where('tag_corresponds.correspond_type', Video::class)
                    ->whereIn('tag_corresponds.correspond_id', $video_ids)
                    ->groupBy('tag_corresponds.tag_id')
                    ->get()
                    ->toArray();
            //----------------------------------------------------------------------
            // image
            $image_ids = TagCorrespond::where('correspond_type', ImageGroup::class)
                    ->where('tag_id', $tag->id)->pluck('correspond_id')->toArray();
            // 撈出該圖片下所有標籤id與次數
            $image_all_ids = TagCorrespond::selectRaw('tag_corresponds.tag_id, tags.name, count(*) as count')
                    ->join('tags', 'tags.id', 'tag_corresponds.tag_id')
                    ->where('tag_corresponds.correspond_type', ImageGroup::class)
                    ->whereIn('tag_corresponds.correspond_id', $image_ids)
                    ->groupBy('tag_corresponds.tag_id')
                    ->get()
                    ->toArray();
            
            $merge_arr = $this -> mergeArray($video_all_ids, $image_all_ids);

            // 按照 count 值進行排序
            usort($merge_arr, function($a, $b) {
                return $b['count'] - $a['count'];
            });

            // 取得前 6 個元素
            if(count($merge_arr) < 6){
                $top6_tags = $merge_arr;
            }else{
                $top6_tags = array_slice($merge_arr, 0, 6);
            }

            // insert DB
            foreach ($top6_tags as $key2 => $top6_tag) {
                $model = new TagPopular();
                $model -> tag_id = $tag -> id;
                $model -> popular_tag_id = $top6_tag['tag_id'];
                $model -> popular_tag_name = $top6_tag['name'];
                $model -> popular_tag_count = $top6_tag['count'];
                $model -> save();
            }
        }          
    }

    protected function mergeArray($array1, $array2){
        // 建立用於儲存結果的陣列
        $result = [];

        // 將$array1的元素加入$result
        foreach ($array1 as $item) {
            $tagId = $item['tag_id'];
            $count = $item['count'];

            if (isset($result[$tagId])) {
                $result[$tagId]['count'] += $count;
            } else {
                $result[$tagId] = [
                    'tag_id' => $tagId, 
                    'name' => trim($item['name']),
                    'count' => $count
                ];
            }
        }

        // 將$array2的元素加入$result
        foreach ($array2 as $item) {
            $tagId = $item['tag_id'];
            $count = $item['count'];

            if (isset($result[$tagId])) {
                $result[$tagId]['count'] += $count;
            } else {
                $result[$tagId] = [
                    'tag_id' => $tagId, 
                    'name' => trim($item['name']),
                    'count' => $count
                ];
            }
        }

        // 將$result的值轉為索引陣列
        $result = array_values($result);

        return $result;
    }
}
