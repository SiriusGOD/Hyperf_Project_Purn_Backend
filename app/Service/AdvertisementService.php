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

use App\Model\Advertisement;
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class AdvertisementService
{
    public const CACHE_KEY = 'advertisement';

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 取得廣告
    public function getAdvertisements(): array
    {
        if ($this->redis->exists(self::CACHE_KEY)) {
            $jsonResult = $this->redis->get(self::CACHE_KEY);
            return json_decode($jsonResult, true);
        }

        $now = Carbon::now()->toDateTimeString();
        $result = Advertisement::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('expire', Advertisement::EXPIRE['no'])
            ->get()
            ->toArray();

        $this->redis->set(self::CACHE_KEY, json_encode($result));

        return $result;
    }

    public function getAdvertisementBySearch(int $page, int $limit = 1): array
    {
        if ($limit == 0) {
            return [];
        }
        $models = $this->getAdvertisements();
        if(empty($models)) {
            return [];
        }
        $count = count($models);
        if ($limit >= $count) {
            return $models;
        }

        $key = ($page * $limit) % $count;

        $result = [];



        for($i = 0; $i<$limit; $i++) {
            $data = $models[$key] ?? null;
            if ($data == null) {
                $key = 0;
                $data = $models[$key];
            }
            $result[] = $data;
            $key++;
        }

        return $result;
    }

    // 更新快取
    public function updateCache(): void
    {
        $now = Carbon::now()->toDateTimeString();
        $result = Advertisement::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('expire', Advertisement::EXPIRE['no'])
            ->get()
            ->toArray();

        $this->redis->set(self::CACHE_KEY, json_encode($result));
    }

    // 新增或更新廣告
    public function storeAdvertisement(array $data): void
    {
        $model = Advertisement::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->name = $data['name'];
        if (! empty($data['image_url'])) {
            $model->image_url = $data['image_url'];
            $model->height = $data['height'];
            $model->weight = $data['weight'];
        }
        $model->url = $data['url'];
        $model->position = $data['position'];
        $model->start_time = $data['start_time'];
        $model->end_time = $data['end_time'];
        $model->buyer = $data['buyer'];
        $model->expire = $data['expire'];
        $model->save();
        $this->updateCache();
    }
}
