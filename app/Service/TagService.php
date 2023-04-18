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

use App\Model\MemberTag;
use App\Model\Tag;
use App\Model\TagCorrespond;
use App\Model\TagHasGroup;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

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
        return Tag::all();
    }

    public function createTag(array $params): void
    {
        $model = new Tag();
        if (! empty($params['id'])) {
            $model = Tag::find($params['id']);
        }
        $model->name = $params['name'];
        $model->user_id = $params['user_id'];
        $model->hot_order = $params['hot_order'];
        $model->save();

        if (count($params['groups']) > 0) {
            $id = $model->id;
            $this->createTagGroupRelationship($params['group_id'], $id);
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
                    self::createTagRelationship('video', $videoId, $tag->id);
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
            $this->createTagRelationship($className, $classId, $tagId);
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
}
