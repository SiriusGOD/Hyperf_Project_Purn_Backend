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

use App\Constants\Constants;
use App\Model\Actor;
use App\Model\ActorCorrespond;
use App\Model\ActorHasClassification;
use App\Model\ImageGroup;
use App\Model\MemberFollow;
use App\Model\Video;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class ActorService extends GenerateService
{
    public const CACHE_KEY = 'actor';

    public const COUNT_KEY = 'actor_count';

    public const EXPIRE = 600;

    public const COUNT_EXPIRE = 180;

    public const TTL_ONE_DAY = 86400;

    protected Redis $redis;
    protected $model;
    public function __construct(Redis $redis, Actor $actor)
    {
        $this->redis = $redis;
        $this->model = $actor;
    }

    // 影片演員關聯
    public function videoCorrespondActor(array $data, int $videoId)
    {
        if ($data['tags'] != '') {
            $ids = [];
            $tags = explode(',', $data['actors']);
            foreach ($tags as $v) {
                $d['name'] = $v;
                $d['user_id'] = 1;
                $d['sex'] = 0;
                $actor = self::storeActor($d);
                $res = self::createActorRelationship(Video::class, $videoId, $actor->id);
                $ids['actorCorresponds'][] = $res->id;
                $ids['actors'][] = $actor->id;
            }
            return $ids;
        }
    }

    // 取得演員
    public function getActors(int $page, int $userId = 0): array
    {
        $query = $this->model->offset(Actor::PAGE_PER * $page)->limit(Actor::PAGE_PER)->get()->toArray();
        foreach ($query as $key => $value) {
            $actor_id = $value['id'];
            // 獲取該演員參與的影片數與圖片數
            $count_arr = ActorCorrespond::select('correspond_type', Db::raw('count(*) as count'))
                ->where('actor_id', $actor_id)
                ->groupBy(['actor_id', 'correspond_type'])
                ->get()->toArray();
            foreach ($count_arr as $key2 => $value2) {
                switch ($value2['correspond_type']) {
                    case Video::class:
                        $query[$key]['video_count'] = $value2['count'];
                        break;
                    case ImageGroup::class:
                        $query[$key]['image_count'] = $value2['count'];
                        break;
                }
            }

            if (empty($query[$key]['video_count'])) {
                $query[$key]['video_count'] = 0;
            }
            if (empty($query[$key]['image_count'])) {
                $query[$key]['image_count'] = 0;
            }

            // 確認該演員是否有被使用者追蹤
            if (MemberFollow::where('member_id', $userId)->where('correspond_type', Actor::class)->where('correspond_id', $actor_id)->whereNull('deleted_at')->exists()) {
                $query[$key]['is_follow'] = 1;
            } else {
                $query[$key]['is_follow'] = 0;
            }

            // avatar加上網域
            if (! empty($value['avatar'])) {
                $query[$key]['avatar'] = \Hyperf\Support\env('IMAGE_GROUP_ENCRYPT_URL', 'https://new.eewwwn.cn') . $value['avatar'];
            }
        }
        return $query;
    }

    // 計算總數
    public function getCount()
    {
        return Actor::count();
    }

    // 計算總數 存Redis
    public function getActorCount()
    {
        if ($this->redis->exists(self::COUNT_KEY)) {
            $jsonResult = $this->redis->get(self::COUNT_KEY);
            return json_decode($jsonResult, true);
        }
        $result = (string) self::getCount();
        $this->redis->set(self::COUNT_KEY, $result, self::COUNT_EXPIRE);
        return $result;
    }

    // 共用自取
    public function selfGet($offset = 0, $limit = 0)
    {
        return Actor::select('id', 'sex', 'name', 'created_at')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // 更新快取
    public function updateCache(): void
    {
        $result = self::selfGet();
        $this->redis->set(self::CACHE_KEY . '0,0', json_encode($result), self::EXPIRE);
    }

    // 新增或更新演員
    public function storeActor(array $data)
    {
        $name = $data['name'];
        $data['classifications'] = 12;
        if (Actor::where('name', $name)->exists()) {
            $model = Actor::where('name', $name )->first();
              $ahc = ActorHasClassification::where('actor_id',$model->id)->first(); 
            if($ahc){
              $data['classifications'] = $ahc->actor_classifications_id;
            }
        } else {
            $model = new Actor();
        }
        $model->user_id = $data['user_id'];
        $model->name = $data['name'];
        $model->sex = $data['sex'];
        $model->avatar = $data['image_url'] ?? '';
        $model->save();

        // 新增或更新演員分類關係
        $model = Actor::where('name', $data['name'])->first();
        $arr_classify['actor_classifications_id'] = $data['classifications'];
        $this->createActorClassificationRelationship($arr_classify, $model->id);
        $this->delFrontCache();

        return $model;
    }

    public function findActor(string $name)
    {
        if (Actor::where('name', $name)->exists()) {
            return Actor::where('name', $name)->first();
        }
        return false;
    }

    // 新熷 演員關係
    public function createActorRelationship(string $className, int $classId, int $actorId)
    {
        $model = ActorCorrespond::where('correspond_type', $className)
            ->where('correspond_id', $classId)
            ->where('actor_id', $actorId);
        if (! $model->exists()) {
            $model = new ActorCorrespond();
            $model->correspond_type = $className;
            $model->correspond_id = $classId;
            $model->actor_id = $actorId;
            $model->save();
            return $model;
        }
        return $model->first();
    }

    // 新增或更新演員分類關係
    public function createActorClassificationRelationship(array $classification, int $actorId)
    {
        ActorHasClassification::where('actor_id', $actorId)->delete();
        foreach ($classification as $key => $value) {
            $model = ActorHasClassification::where('actor_classifications_id', $value)
                ->where('actor_id', $actorId);
            if (! $model->exists()) {
                $model = new ActorHasClassification();
                $model->actor_id = $actorId;
                $model->actor_classifications_id = $value;
                $model->save();
            }
        }

        $this->delFrontCache();
    }

    // 獲取演員詳細資料
    public function getActorDetail(int $actor_id, int $userId = 0)
    {
        $data = [];
        // 撈取基本資料
        $actor = Actor::select('name', 'avatar')->where('id', $actor_id)->first()->toArray();
        $data['name'] = $actor['name'];
        
        // 撈取作品數
        $works = ActorCorrespond::selectRaw('correspond_type, correspond_id, count(*) as count')->where('actor_id', $actor_id)->groupBy('correspond_type')->get();
        foreach ($works as $key => $value) {
            if ($value->correspond_type == Video::class) {
                $data['video_num'] = $value->count;
            }
            if ($value->correspond_type == ImageGroup::class) {
                $data['image_num'] = $value->count;
            }
        }
        if (empty($data['video_num'])) {
            $data['video_num'] = 0;
        }
        if (empty($data['image_num'])) {
            $data['image_num'] = 0;
        }

        // 處理大頭貼
        if (! empty($actor['avatar'])) {
            $data['avatar'] = env('IMAGE_GROUP_ENCRYPT_URL') . $actor['avatar'];
        }else{
            // 沒大頭貼時撈取作品封面圖
            switch ($works[0] -> correspond_type) {
                case ImageGroup::class:
                    $thumb = ImageGroup::selectRaw('thumbnail as thumb')->where('id', $works[0]->correspond_id)->first();
                    break;
                case Video::class:
                    $thumb = Video::selectRaw('cover_thumb as thumb')->where('id', $works[0]->correspond_id)->first();
                    break;
            }
            if(empty($thumb)){
                $data['avatar'] = "";
            }else{
                $data['avatar'] = env('IMAGE_GROUP_ENCRYPT_URL').$thumb->thumb;
            }
        }

        // 查詢是否追隨
        if (MemberFollow::where('member_id', $userId)->where('correspond_type', Actor::class)->where('correspond_id', $actor_id)->whereNull('deleted_at')->exists()) {
            $data['is_follow'] = 1;
        } else {
            $data['is_follow'] = 0;
        }

        return $data;
    }

    // 判斷演員是否有追蹤
    public function isFollow($memberId, $id)
    {
        $redisKey = self::CACHE_KEY . ':isExist:' . $memberId;
        if ($this->redis->exists($redisKey)) {
            $arr = json_decode($this->redis->get($redisKey), true);
        } else {
            $follows = MemberFollow::where('member_id', $memberId)->where('correspond_type', Actor::class)->whereNull('deleted_at')->select('correspond_id')->get()->toArray();
            if (empty($follows)) {
                return 0;
            }

            $arr = [];
            foreach ($follows as $key => $value) {
                array_push($arr, $value['correspond_id']);
            }

            $this->redis->set($redisKey, json_encode($arr));
            $this->redis->expire($redisKey, self::TTL_ONE_DAY);
        }
        // 是否追蹤
        if (in_array($id, $arr)) {
            // 是
            return 1;
        }
        // 否
        return 0;
    }

    // 更新會員的演員追蹤快取
    public function updateIsExistCache($memberId): void
    {
        $redisKey = self::CACHE_KEY . ':isExist:' . $memberId;
        $follows = MemberFollow::where('member_id', $memberId)->where('correspond_type', Actor::class)->whereNull('deleted_at')->select('correspond_id')->get()->toArray();

        $arr = [];
        foreach ($follows as $key => $value) {
            array_push($arr, $value['correspond_id']);
        }
        $this->redis->set($redisKey, json_encode($arr));
        $this->redis->expire($redisKey, self::TTL_ONE_DAY);
    }

    public function searchByActorId(array $params): array
    {
        $query = ActorCorrespond::where('actor_id', $params['id'])
            ->offset($params['page'] * $params['limit'])
            ->limit($params['limit']);

        if (! empty($params['sort_by']) and $params['sort_by'] == Constants::SORT_BY['click']) {
            if ($params['is_asc'] == 1) {
                $query = $query->orderBy('total_click');
            } else {
                $query = $query->orderByDesc('total_click');
            }
        } elseif (! empty($params['sort_by']) and $params['sort_by'] == Constants::SORT_BY['created_time']) {
            if ($params['is_asc'] == 1) {
                $query = $query->orderBy('id');
            } else {
                $query = $query->orderByDesc('id');
            }
        }

        if (! empty($params['filter'])) {
            $query = $query->where('correspond_type', $params['filter']);
            $hideIds = ReportService::getHideIds($params['filter']);
            $query = $query->whereNotIn('correspond_id', $hideIds);
            $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds($params['filter']);
            $query = $query->whereIn('correspond_id', $enableIds);
        } else {
            $videoHideIds = ReportService::getHideIds(Video::class);
            $imageGroupHideIds = ReportService::getHideIds(ImageGroup::class);
            $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(Video::class);
            $actorVideoHideIds = ActorCorrespond::where('correspond_type', Video::class)
                ->whereIn('correspond_id', $videoHideIds)
                ->get()
                ->pluck('id')
                ->toArray();
            $actorVideoEnableIds = ActorCorrespond::where('correspond_type', Video::class)
                ->whereIn('correspond_id', $enableIds)
                ->get()
                ->pluck('id')
                ->toArray();

            $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(ImageGroup::class);
            $actorImageGroupHideIds = ActorCorrespond::where('correspond_type', ImageGroup::class)
                ->whereIn('correspond_id', $imageGroupHideIds)
                ->get()
                ->pluck('id')
                ->toArray();

            $actorImageGroupEnableIds = ActorCorrespond::where('correspond_type', ImageGroup::class)
                ->whereIn('correspond_id', $enableIds)
                ->get()
                ->pluck('id')
                ->toArray();

            $query = $query->whereNotIn('id', array_merge($actorImageGroupHideIds, $actorVideoHideIds))
            ->whereIn('id', array_merge($actorImageGroupEnableIds, $actorVideoEnableIds));
        }

        $models = $query->get();
        if (empty($models)) {
            return [];
        }
        $models = $models->toArray();
        $result = [];

        $result = $this->getVideoDetail($models, $result);

        $collect = \Hyperf\Collection\collect($this->getImageGroupsDetail($models, $result));

        if (! empty($params['sort_by']) and $params['sort_by'] == Constants::SORT_BY['click']) {
            if ($params['is_asc'] == 1) {
                $collect = $collect->sortBy('total_click');
            } else {
                $collect = $collect->sortByDesc('total_click');
            }
        } elseif (! empty($params['sort_by']) and $params['sort_by'] == Constants::SORT_BY['created_time']) {
            if ($params['is_asc'] == 1) {
                $collect = $collect->sortBy('created_at');
            } else {
                $collect = $collect->sortByDesc('created_at');
            }
        }

        $rows = [];
        foreach ($collect->toArray() as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    // 刪除前台演員分類的快取
    protected function delFrontCache()
    {
        $service = make(ActorClassificationService::class);
        $service->delRedis();
    }

    protected function getVideoDetail(array $models, array $data): array
    {
        $ids = [];
        foreach ($models as $model) {
            if ($model['correspond_type'] == Video::class) {
                $ids[] = $model['correspond_id'];
            }
        }

        $videos = Video::with('tags')->whereIn('id', $ids)->get()->toArray();

        $result = [];
        foreach ($videos as $video) {
            foreach ($models as $model) {
                if ($model['correspond_id'] == $video['id'] and $model['correspond_type'] == Video::class) {
                    $result[] = $video;
                }
            }
        }

        return $this->generateVideos($data, $result);
    }

    protected function getImageGroupsDetail(array $models, array $data): array
    {
        $ids = [];
        foreach ($models as $model) {
            if ($model['correspond_type'] == ImageGroup::class) {
                $ids[] = $model['correspond_id'];
            }
        }

        $imageGroups = ImageGroup::with(['imagesLimit', 'tags'])->whereIn('id', $ids)->get()->toArray();

        $result = [];
        foreach ($imageGroups as $imageGroup) {
            foreach ($models as $model) {
                if ($model['correspond_id'] == $imageGroup['id'] and $model['correspond_type'] == ImageGroup::class) {
                    $result[] = $imageGroup;
                }
            }
        }

        return $this->generateImageGroups($data, $result);
    }
}
