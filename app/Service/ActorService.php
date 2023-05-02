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
use App\Model\MemberFollow;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class ActorService
{
    public const CACHE_KEY = 'actor';

    public const COUNT_KEY = 'actor_count';

    public const EXPIRE = 600;

    public const COUNT_EXPIRE = 180;

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
                $res = self::createActorRelationship('video', $videoId, $actor->id);
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
                    case 'video':
                        $query[$key]['video_count'] = $value2['count'];
                        break;
                    case 'image':
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

        return $model;
    }

    // 新增或更新演員
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
    }
}
