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
use App\Model\ActorCorrespond;
use App\Model\MemberFollow;
use App\Model\Actor;
use App\Model\ActorHasClassification;
use App\Model\Click;
use App\Model\ImageGroup;
use App\Model\Video;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;
use Carbon\Carbon;

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
    public function getListByClassification(int $type_id, int $userId = 0)
    {
        // redis
        $checkRedisKey = self::CACHE_KEY.":".Carbon::now()->toDateString().":".$type_id;
        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $res_arr = [];
        if (empty($type_id)) {
            $type_arr = $this->getClassification();
            // 撈取每個分類總影片點擊率前四
            foreach ($type_arr as $key => $value) {
                $classify_id = $value['id'];
                $query = ActorHasClassification::join('actors', 'actors.id', 'actor_has_classifications.actor_id')
                                        ->join('actor_corresponds', 'actor_has_classifications.actor_id', 'actor_corresponds.actor_id')
                                        ->select('actors.id', 'actors.name', 'actors.avatar')
                                        ->where('actor_has_classifications.actor_classifications_id', $classify_id)
                                        ->whereNull('actor_corresponds.deleted_at')
                                        ->groupBy('actors.id');
                $total = $query->get()->count();
                $query = $query->get()->toArray();
                if (!empty($query)) {
                    // 查詢是否追隨與作品數
                    foreach ($query as $key => $value2) {
                        $actor_id = $value2['id'];
                        $name = trim($value2['name']);
                        // 查詢是否追隨
                        if(MemberFollow::where('member_id', $userId)->where('correspond_type', Actor::class)->where('correspond_id', $actor_id)->whereNull('deleted_at')->exists()){
                            $query[$key]['is_follow'] = 1;
                        }else{
                            $query[$key]['is_follow'] = 0;
                        }

                        // avatar加上網域
                        if(!empty($value2['avatar'])){
                            $query[$key]['avatar'] = env('IMAGE_GROUP_ENCRYPT_URL').$value2['avatar'];
                        }else{
                            $query[$key]['avatar'] = "";
                        };

                        // 查詢作品數
                        $works_query = ActorCorrespond::where('actor_id', $actor_id)->whereNull('deleted_at')->get();
                        $numberOfWorks = $works_query -> count();
                        $query[$key]['numberOfWorks'] = $numberOfWorks;

                        // 擷取名稱第一個字
                        $letter =  mb_substr($name, 0, 1, 'UTF-8');
                        $query[$key]['letter'] = $letter;

                        // 查詢該演員的點擊數 最多取4位
                        $clicks = ActorCorrespond::join('clicks', function ($join) {
                                                    $join->on('clicks.type', 'actor_corresponds.correspond_type')
                                                        ->on('clicks.type_id', 'actor_corresponds.correspond_id');
                                                })
                                                ->select(DB::raw('sum(clicks.count) as count'))
                                                ->where('actor_corresponds.actor_id', $actor_id)
                                                ->first();
                        if(empty($clicks -> count)){
                            $query[$key]['click_num'] = 0;
                        }else{
                            $query[$key]['click_num'] = $clicks -> count;
                        }
                    }

                    // 排序
                    usort($query, function($a, $b) {
                        return $b['click_num'] - $a['click_num'];
                    });
                    if(count($query) > 4)$query = array_slice($query, 0, 4);
                    
                    foreach ($query as $key3 => $value3) {
                        unset($query[$key3]['click_num']);
                    }

                    array_push($res_arr, [
                        'type_id' => $classify_id,
                        'type_name' => $value['name'],
                        'type_total' => $total,
                        'type_data' => $query,
                    ]);
                }
            }
        } else {
            $type = ActorClassification::find($type_id)->toArray();
            $query = ActorHasClassification::join('actors', 'actors.id', 'actor_has_classifications.actor_id')
                                        ->join('actor_corresponds', 'actor_has_classifications.actor_id', 'actor_corresponds.actor_id')
                                        ->where('actor_has_classifications.actor_classifications_id', $type_id)
                                        ->whereNull('actor_corresponds.deleted_at')
                                        ->select('actors.id', 'actors.name', 'actors.avatar')
                                        ->groupBy('actors.id');
            $total = $query->get()->count();
            $query = $query->get();
            if (!empty($query)) {
                $query = $query->toArray();
                // 查詢是否追隨與作品數
                $popular_arr = [];
                foreach ($query as $key => $value) {
                    $actor_id = $value['id'];
                    $name = trim($value['name']);
                    // 查詢是否追隨
                    if(MemberFollow::where('member_id', $userId)->where('correspond_type', Actor::class)->where('correspond_id', $actor_id)->whereNull('deleted_at')->exists()){
                        $query[$key]['is_follow'] = 1;
                    }else{
                        $query[$key]['is_follow'] = 0;
                    }

                    // avatar加上網域
                    if(!empty($value['avatar'])){
                        $query[$key]['avatar'] = env('IMAGE_GROUP_ENCRYPT_URL').$value['avatar'];
                    }else{
                        $query[$key]['avatar'] = '';
                    }

                    // 查詢作品數
                    $numberOfWorks = ActorCorrespond::where('actor_id', $actor_id)->whereNull('deleted_at')->count();
                    $query[$key]['numberOfWorks'] = $numberOfWorks;

                    // 擷取名稱第一個字
                    $letter =  mb_substr($name, 0, 1, 'UTF-8');
                    $query[$key]['letter'] = $letter;

                    // 查詢該演員的點擊數 最多取８位
                    $actor_arr = $query[$key];
                    $click_num = 0;
                    $seven_days = Carbon::now()->subDays(7)->toDateString();
                    $clicks = ActorCorrespond::where('actor_id', $actor_id)->whereNull('deleted_at')->get();
                    foreach ($clicks as $key => $value) {
                        switch ($value -> correspond_type) {
                            case ImageGroup::class:
                                $count = Click::join('click_details', 'clicks.id', 'click_details.click_id')
                                            ->where('click_details.created_at', '>=', $seven_days)
                                            ->where('clicks.type', ImageGroup::class)
                                            ->where('clicks.type_id', $value -> correspond_id)
                                            ->count();
                                break;
                            case Video::class:
                                $count = Click::join('click_details', 'clicks.id', 'click_details.click_id')
                                            ->where('click_details.created_at', '>=', $seven_days)
                                            ->where('clicks.type', Video::class)
                                            ->where('clicks.type_id', $value -> correspond_id)
                                            ->count();
                                break;
                            default:
                                $count = 0;
                                break;
                        }
                        $click_num += $count;
                    }
                    
                    $actor_arr['click_num'] = $click_num;
                    array_push($popular_arr, $actor_arr);
                }
                // 排序
                usort($popular_arr, function($a, $b) {
                    return $b['click_num'] - $a['click_num'];
                });
                if(count($popular_arr) > 8)$popular_arr = array_slice($popular_arr, 0, 8);
                
                foreach ($popular_arr as $key => $value) {
                    unset($popular_arr[$key]['click_num']);
                }

                // 移除作品數為0的演員
                $array = array_filter($query, function($item) {
                    return $item['numberOfWorks'] != 0;
                });
                $array = array_values($array);

                array_push($res_arr, [
                    'type_id' => $type_id,
                    'type_name' => $type['name'],
                    'type_total' => $total,
                    'type_data' => $array,
                    'popular_data' => $popular_arr
                ]);
            }
        }

        $this->redis->set($checkRedisKey, json_encode($res_arr));
        $this->redis->expire($checkRedisKey, self::TTL_ONE_DAY);

        return $res_arr;
    }

    //刪除Redis
    public function delRedis(){
        $checkRedisKey = self::CACHE_KEY.":".Carbon::now()->toDateString();
        $keys = $this->redis->keys( $checkRedisKey.'*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }
}
