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

use App\Model\Actor;
use App\Model\ActorCorrespond;
use App\Model\ActorHasClassification;
use App\Model\Image;
use App\Model\ImageGroup;
use App\Model\MemberFollow;
use App\Model\Video;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class ActorService
{
    public const CACHE_KEY = 'actor';

    public const COUNT_KEY = 'actor_count';

    public const EXPIRE = 600;

    public const COUNT_EXPIRE = 180;

    public const TTL_ONE_DAY = 86400;

    protected Redis $redis;

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
            $tags = explode(',', $data['tags']);
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
                        //TODO 修改計算成套圖數量
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
            if(MemberFollow::where('member_id', $userId)->where('correspond_type', Actor::class)->where('correspond_id', $actor_id)->whereNull('deleted_at')->exists()){
                $query[$key]['is_follow'] = 1;
            }else{
                $query[$key]['is_follow'] = 0;
            }

            // avatar加上網域
            if(!empty($value['avatar']))$query[$key]['avatar'] = env('IMG_DOMAIN').$value['avatar'];
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
        if (Actor::where('name', $data['name'])->exists()) {
            $model = Actor::where('name', $data['name'])->first();
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
        $arr_classify = $data['classifications'] ?? [];
        $this->createActorClassificationRelationship($arr_classify, $model->id);
        $this -> delFrontCache();

        return $model;
    }

    // 
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
        
        $this -> delFrontCache();
    }

    // 獲取演員詳細資料
    public function getActorDetail(int $actor_id, int $userId = 0)
    {
        $data = [];
        // 撈取基本資料
        $actor = Actor::select('name', 'avatar')->where('id', $actor_id)->first()->toArray();
        $data['name'] = $actor['name'];
        $data['avatar'] = $actor['avatar'];
        if(!empty($actor['avatar']))$data['avatar'] = env('IMG_DOMAIN').$actor['avatar'];
        // 撈取作品數
        $works = ActorCorrespond::selectRaw('correspond_type, count(*) as count')->where('actor_id', $actor_id)->groupBy('correspond_type')->get();
        foreach ($works as $key => $value) {
            if($value -> correspond_type == Video::class)$data['video_num'] = $value->count;
            if($value -> correspond_type == ImageGroup::class)$data['image_num'] = $value->count;
        }
        if(empty($data['video_num']))$data['video_num'] = 0;
        if(empty($data['image_num']))$data['image_num'] = 0;

        // 查詢是否追隨
        if(MemberFollow::where('member_id', $userId)->where('correspond_type', Actor::class)->where('correspond_id', $actor_id)->whereNull('deleted_at')->exists()){
            $data['is_follow'] = 1;
        }else{
            $data['is_follow'] = 0;
        }

        return $data;
    }

    // 刪除前台演員分類的快取
    protected function delFrontCache()
    {
        $service = make(ActorClassificationService::class);
        $service->delRedis();
    }

    // 判斷演員是否有追蹤
    public function isExist($memberId, $id)
    {
        $redisKey = self::CACHE_KEY.':isExist:'.$memberId;
        if ($this->redis->exists($redisKey)) {
            $arr = json_decode($this->redis->get($redisKey), true);
        }else{
            $follows = MemberFollow::where('member_id', $memberId)->where('correspond_type', Actor::class)->select('correspond_id')->get()->toArray();
            if(empty($follows)) return 0;

            $arr = [];
            foreach ($follows as $key => $value) {
                array_push($arr, $value['correspond_id']);
            }

            $this->redis->set($redisKey, json_encode($arr));
            $this->redis->expire($redisKey, self::TTL_ONE_DAY);
        }
        // 是否追蹤
        if(in_array($id, $arr)){
            // 是
            return 1;
        }else{
            // 否
            return 0;
        }
    }

    // 更新會員的演員追蹤快取
    public function updateIsExistCache($memberId): void
    {
        $redisKey = self::CACHE_KEY.':isExist:'.$memberId;
        $follows = MemberFollow::where('member_id', $memberId)->where('correspond_type', Actor::class)->select('correspond_id')->get()->toArray();

        $arr = [];
        foreach ($follows as $key => $value) {
            array_push($arr, $value['correspond_id']);
        }
        $this->redis->set($redisKey, json_encode($arr));
        $this->redis->expire($redisKey, self::TTL_ONE_DAY);
    }
}
