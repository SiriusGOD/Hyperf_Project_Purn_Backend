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

use App\Model\ActorClassification;
use App\Model\Actor;
use App\Model\ActorCorrespond;
use App\Model\ActorHasClassification;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class ActorClassificationService
{
    public const CACHE_KEY = 'actor_classification';

    public const TTL_ONE_DAY = 86400;

    public const GET_ACTOR_COUNT = 4;

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 更新快取
    public function updateCache(): void
    {
        $result = ActorClassification::select('id', 'sort', 'name')->orderBy('sort')->get()->toArray();
        $this->redis->set(self::CACHE_KEY, json_encode($result));
        $this->redis->expire(self::CACHE_KEY, self::TTL_ONE_DAY);
    }

    // 新增或更新分類
    public function storeActorClassification(array $data): void
    {
        $model = ActorClassification::findOrNew($data['id']);
        $model->sort = $data['sort'];
        $model->user_id = $data['user_id'];
        $model->name = $data['name'];
        $model->save();
        $this->updateCache();
    }

    // 獲取分類資料
    public function getClassification()
    {
        $checkRedisKey = self::CACHE_KEY;

        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $query = ActorClassification::select('id', 'sort', 'name')->orderBy('sort')->get()->toArray();

        $this->redis->set($checkRedisKey, json_encode($query));
        $this->redis->expire($checkRedisKey, self::TTL_ONE_DAY);

        return $query;
    }

    // 獲取依照分類的演員資料
    public function getListByClassification(int $type_id)
    {
        $res_arr = [];
        if(empty($type_id)){
            $type_arr = $this->getClassification();
            // 撈取每個分類總影片點擊率前四
            foreach ($type_arr as $key => $value) {
                $classify_id = $value['id'];
                $query = ActorCorrespond::join('videos', function ($join) {
                    $join->on('actor_corresponds.correspond_id', '=', 'videos.id')
                        ->where('actor_corresponds.correspond_type', '=', 'video');
                })
                ->join('actors', 'actor_corresponds.actor_id', 'actors.id')
                ->join('actor_has_classifications', 'actors.id', 'actor_has_classifications.actor_id')
                ->select('actors.id', 'actors.sex', 'actors.name', 'actors.avatar', DB::raw('sum(videos.rating) as video_click_num'))
                ->where('actor_has_classifications.actor_classifications_id', $classify_id)
                ->groupBy('actor_corresponds.actor_id')
                ->orderBy('video_click_num', 'desc')
                ->limit(self::GET_ACTOR_COUNT)
                ->get()->toArray();
                if(count($query) > 0){
                    array_push($res_arr, array(
                        'type_id' => $classify_id,
                        'type_name' => $value['name'],
                        'type_data' => $query
                    ));
                }
            }
        }else{
            $type = ActorClassification::find($type_id)->toArray();
            $query = ActorCorrespond::join('videos', function ($join) {
                $join->on('actor_corresponds.correspond_id', '=', 'videos.id')
                    ->where('actor_corresponds.correspond_type', '=', 'video');
            })
            ->join('actors', 'actor_corresponds.actor_id', 'actors.id')
            ->join('actor_has_classifications', 'actors.id', 'actor_has_classifications.actor_id')
            ->select('actors.id', 'actors.sex', 'actors.name', DB::raw('sum(videos.rating) as video_click_num'))
            ->where('actor_has_classifications.actor_classifications_id', $type_id)
            ->groupBy('actor_corresponds.actor_id')
            ->orderBy('video_click_num', 'desc')
            ->get()->toArray();
            if(count($query) > 0){
                array_push($res_arr, array(
                    'type_id' => $type_id,
                    'type_name' => $type['name'],
                    'type_data' => $query
                ));
            }
        } 
        return $res_arr;
    }
}
