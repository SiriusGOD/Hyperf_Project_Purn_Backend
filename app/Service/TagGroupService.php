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
use App\Model\TagGroup;
use App\Model\Video;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

use function Hyperf\Support\env;

class TagGroupService
{
    public const CACHE_KEY = 'tag_group';
    public const TAG_GROUP_KEY = 'tag_group_search';

    public const TTL_ONE_DAY = 86400;
    public const TTL_30_MIN = 1800;

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

     // 更新快取
     public function updateCache(): void
     {
         $result = TagGroup::where('is_hide', 0)->get()->toArray();
         $this->redis->set(self::CACHE_KEY, json_encode($result));
         $this->redis->expire(self::CACHE_KEY, self::TTL_ONE_DAY);
     }

    public function getTags()
    {
        $checkRedisKey = self::CACHE_KEY;

        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $query = TagGroup::select('id', 'name')->where('is_hide', 0)->get()->toArray();

        $this->redis->set($checkRedisKey, json_encode($query));
        $this->redis->expire($checkRedisKey, self::TTL_ONE_DAY);

        return $query;
    }

    public function storeTagGroup(array $data): void
    {
        $model = TagGroup::findOrNew($data['id']);
        $model->name = $data['name'];
        $model->user_id = $data['user_id'];
        $model->is_hide = $data['is_hide'];
        $model->save();
        $this->updateCache();
    }

    public function searchGroupTags(int $group_id)
    {
        $checkRedisKey = self::TAG_GROUP_KEY.':'.$group_id;

        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $tags = TagGroup::join('tag_has_groups', 'tag_groups.id', 'tag_has_groups.tag_group_id')
                         ->join('tags', 'tag_has_groups.tag_id', 'tags.id')
                         ->join('tag_corresponds', 'tags.id', 'tag_corresponds.tag_id')
                         ->select('tag_corresponds.tag_id', 'tags.name as tag_name', 'tags.img', DB::raw('count(*) as product_num'))
                         ->where('tag_groups.id', $group_id)
                         ->whereIn('tag_corresponds.correspond_type', [Video::class,ImageGroup::class])
                         ->groupBy('tag_corresponds.tag_id')
                         ->get()->toArray();
        $result = [];
        foreach ($tags as $key => $value) {
            if(!empty($value['img'])){
                $tags[$key]['img'] = env('IMAGE_GROUP_ENCRYPT_URL') . $value['img'];
            }
            else{
                $tags[$key]['img'] = "";
            }
            // 作品數大於０才顯示 
            if($value['product_num'] > 0){
                array_push($result, array(
                    'tag_id' => $tags[$key]['tag_id'],
                    'tag_name' => $tags[$key]['tag_name'],
                    'img' => $tags[$key]['img'],
                    'product_num' => $tags[$key]['product_num']
                ));
            }
        }
        

        $this->redis->set($checkRedisKey, json_encode($result));
        $this->redis->expire($checkRedisKey, self::TTL_30_MIN);

        return $result;
    }
}
