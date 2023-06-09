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

use App\Model\MemberLevel;
use Hyperf\Redis\Redis;

class MemberLevelService
{
    public const CACHE_KEY = 'member_level';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function store(array $params): void
    {
        $model = MemberLevel::where('id', $params['id'])->first();
        if (empty($model)) {
            $model = new MemberLevel();
        }
        $model->user_id = $params['user_id'];
        $model->type = $params['type'];
        $model->name = $params['name'];
        $model->title = $params['title'];
        $model->description = $params['description'];
        $model->remark = $params['remark'];
        $model->duration = $params['duration'];
        $model->save();

        $this->delMemberProductKey();
    }

    public function delMemberProductKey()
    {
        $checkRedisKey = "product:member:";
        $keys = $this->redis->keys( $checkRedisKey.'*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }
}
