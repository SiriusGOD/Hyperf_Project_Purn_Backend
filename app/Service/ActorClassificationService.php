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
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class ActorClassificationService
{
    public const CACHE_KEY = 'actorClassification';

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 取得廣告
    // public function getAdvertisements(): array
    // {
    //     if ($this->redis->exists(self::CACHE_KEY)) {
    //         $jsonResult = $this->redis->get(self::CACHE_KEY);
    //         return json_decode($jsonResult, true);
    //     }

    //     $now = Carbon::now()->toDateTimeString();
    //     $result = Advertisement::select('id', 'name', 'image_url', 'url', 'position', 'start_time', 'end_time', 'buyer', 'expire', 'created_at', 'updated_at')
    //         ->where('start_time', '<=', $now)
    //         ->where('end_time', '>=', $now)
    //         ->get()
    //         ->toArray();

    //     $this->redis->set(self::CACHE_KEY, json_encode($result));

    //     return $result;
    // }

    // 更新快取
    // public function updateCache(): void
    // {
    //     $now = Carbon::now()->toDateTimeString();
    //     $result = Advertisement::where('start_time', '<=', $now)
    //         ->where('end_time', '>=', $now)
    //         ->where('expire', Advertisement::EXPIRE['no'])
    //         ->get()
    //         ->toArray();

    //     $this->redis->set(self::CACHE_KEY, json_encode($result));
    // }

    // 新增或更新分類
    public function storeActorClassification(string $name, int $user_id): void
    {
        $model = new ActorClassification();
        $model->user_id = $user_id;
        $model->name = $name;
        $model->save();
        // $this->updateCache();
    }
}
