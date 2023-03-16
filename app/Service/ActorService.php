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
use Hyperf\Redis\Redis;

class ActorService
{
    public const CACHE_KEY = 'actor';

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 取得演員
    public function getActors(): array
    {
        if ($this->redis->exists(self::CACHE_KEY)) {
            $jsonResult = $this->redis->get(self::CACHE_KEY);
            return json_decode($jsonResult, true);
        }
        $result = self::selfGet(); 
        $this->redis->set(self::CACHE_KEY, json_encode($result));
        return $result;
    }

    // 共用自取
    public function selfGet(){
        return Actor::select("sex","name",'created_at')
            ->get()
            ->toArray();
    }
  
    // 更新快取
    public function updateCache(): void
    {
        $result = self::selfGet(); 
        $this->redis->set(self::CACHE_KEY, json_encode($result));
    }

    // 新增或更新廣告
    public function storeActor(array $data): void
    {
        $model = Actor::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->name = $data['name'];
        $model->sex = $data['sex'];
        $model->save();
        $this->updateCache();
    }
}

