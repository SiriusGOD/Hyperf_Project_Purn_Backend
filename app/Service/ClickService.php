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

use App\Model\Click;
use App\Model\ClickDetail;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class ClickService
{
    public const POPULAR_DAY = 30;

    public const POPULAR_LIMIT = 100;

    public const POPULAR_CLICK_CACHE_KEY = 'click:';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function addClick(string $type, int $typeId): void
    {
        $today = Carbon::now()->toDateString();
        $model = Click::where('type', $type)
            ->where('type_id', $typeId)
            ->where('statistical_date', $today)
            ->first();

        if (empty($model)) {
            $model = $this->createClick([
                'type' => $type,
                'type_id' => $typeId,
                'statistical_date' => $today,
            ]);
        }

        ++$model->count;
        $model->save();

        if (auth('jwt')->check()) {
            $this->createClickDetail($model->id, auth('jwt')->user()->getId());
        }
    }

    public function calculatePopularClick(string $type, int $limit = self::POPULAR_LIMIT, int $page = 0, array $ids = [])
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(self::POPULAR_DAY);
        $models = Click::where('type', $type)
            ->whereBetween('statistical_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy(['type_id'])
            ->select(DB::raw('type_id as id'), Db::raw('sum(count) as total'))
            ->whereNotIn('type_id', $ids)
            ->orderByDesc('total')
            ->offset($page * $limit)
            ->limit($limit)
            ->get();

        if (! empty($models)) {
            $this->redis->set(self::POPULAR_CLICK_CACHE_KEY . $type, $models->toJson());
        }

        return $models->toArray();
    }

    public function calculatePopularClickByTypeIds(string $type, array $typeIds): array
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(self::POPULAR_DAY);
        $models = Click::where('type', $type)
            ->whereBetween('statistical_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy(['type_id'])
            ->select(DB::raw('type_id as id'), Db::raw('sum(count) as total'))
            ->whereIn('type_id', $typeIds)
            ->get();

        return $models->toArray();
    }

    public function getPopularClick(string $type)
    {
        if ($this->redis->exists(self::POPULAR_CLICK_CACHE_KEY . $type)) {
            return json_decode($this->redis->get(self::POPULAR_CLICK_CACHE_KEY . $type), true);
        }

        return $this->calculatePopularClick($type);
    }

    public function createClickDetail(int $clickId, int $memberId): void
    {
        $model = new ClickDetail();
        $model->click_id = $clickId;
        $model->member_id = $memberId;
        $model->save();
    }

    private function createClick(array $data): Click
    {
        $model = new Click();
        $model->type = $data['type'];
        $model->type_id = $data['type_id'];
        $model->statistical_date = $data['statistical_date'];
        $model->count = 0;

        return $model;
    }
}
