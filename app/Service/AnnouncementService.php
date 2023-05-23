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

use App\Model\Announcement;
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class AnnouncementService
{
    public const CACHE_KEY = 'announcement';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function store(array $params): void
    {
        $model = Announcement::where('id', $params['id'])->first();
        if (empty($model)) {
            $model = new Announcement();
        }

        $model->user_id = $params['user_id'];
        $model->title = $params['title'];
        $model->content = $params['content'];
        $model->start_time = $params['start_time'];
        $model->end_time = $params['end_time'];
        $model->status = $params['status'];
        $model->save();
    }

    public function getAnnouncements(): array
    {
        if ($this->redis->exists(self::CACHE_KEY)) {
            return json_decode($this->redis->get(self::CACHE_KEY), true);
        }

        return $this->updateCache();
    }

    public function updateCache(): array
    {
        $now = Carbon::now()->toDateTimeString();
        $result = Announcement::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('status', Announcement::STATUS['enable'])
            ->orderByDesc('updated_at')
            ->limit(1)
            ->get()
            ->toArray();

        $this->redis->set(self::CACHE_KEY, json_encode($result));

        return $result;
    }
}
