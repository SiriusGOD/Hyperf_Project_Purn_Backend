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
use App\Model\Report;
use App\Model\Video;
use App\Util\General;
use Hyperf\Redis\Redis;

class ReportService
{
    public const CACHE_KEY = 'member_report:';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function createReport(array $params): void
    {
        $model = new Report();
        $model->member_id = $params['member_id'];
        $model->model_type = $params['model_type'];
        $model->model_id = $params['model_id'];
        $model->content = $params['content'];
        $model->type = $params['type'];
        $model->status = $params['status'];
        $model->save();
    }

    public function updateMemberCache(int $memberId): void
    {
        $types = array_flip(Report::MODEL_TYPE);
        $imageGroupKey = self::CACHE_KEY . $types[ImageGroup::class] . ':' . $memberId;
        $videoKey = self::CACHE_KEY . $types[Video::class] . ':' . $memberId;
        $imageIds = Report::where('model_type', Report::MODEL_TYPE['image_group'])
            ->where('member_id', $memberId)
            ->get()
            ->pluck('model_id')
            ->toArray();
        $this->redis->set($imageGroupKey, json_encode($imageIds));

        $videoIds = Report::where('model_type', Report::MODEL_TYPE['video'])
            ->where('member_id', $memberId)
            ->get()
            ->pluck('model_id')
            ->toArray();
        $this->redis->set($videoKey, json_encode($videoIds));
    }

    public static function getHideIds(string $type) : array
    {
        $memberId = auth()->user()->getId();
        $types = array_flip(Report::MODEL_TYPE);
        $redis = make(Redis::class);
        $key = ReportService::CACHE_KEY . $types[$type] . ':' . $memberId;
        if($redis->exists($key)){
            return json_decode($redis->get($key), true);
        }

        return [];
    }

    public function generateReport(array $models) : array
    {
        $result = [];
        foreach ($models as $model) {
            if ($model['model_type'] == Video::class) {
                $video = Video::withTrashed()->where('id', $model['model_id'])->first();
                $model['video_url'] = env('VIDEO_SOURCE_URL', 'https://video.iwanna.tv') . $video->source;
            }
            $result[] = $model;
        }

        return $result;
    }
}
