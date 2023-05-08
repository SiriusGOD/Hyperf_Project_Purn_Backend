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

use App\Model\TagGroup;
use App\Model\Video;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class TagGroupService
{
    public const CACHE_KEY = 'tag_group';

    public const TTL_ONE_DAY = 86400;

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
        // 還缺image計算
        return TagGroup::join('tag_has_groups', 'tag_groups.id', 'tag_has_groups.tag_group_id')
            ->join('tags', 'tag_has_groups.tag_id', 'tags.id')
            ->join('tag_corresponds', 'tags.id', 'tag_corresponds.tag_id')
            ->select('tag_groups.id as group_id', 'tag_groups.name as group_name', 'tag_corresponds.tag_id', 'tags.name as tag_name', DB::raw('count(*) as product_num'))
            ->where('tag_groups.id', $group_id)
            ->where('tag_corresponds.correspond_type', Video::class)
            ->groupBy('tag_corresponds.tag_id')
            ->get()->toArray();
    }
}
