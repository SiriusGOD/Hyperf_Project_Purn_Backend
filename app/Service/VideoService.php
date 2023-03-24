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

use App\Model\ActorCorrespond;
use App\Model\TagCorrespond;
use App\Model\Video;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class VideoService
{
    public const CACHE_KEY = 'video';

    public const COUNT_KEY = 'video_count';

    public const EXPIRE = 600;

    public const COUNT_EXPIRE = 180;

    protected Redis $redis;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }

    // 取得影片
    public function getVideos($offset = 0, $limit = 0): array
    {
        if ($this->redis->exists(self::CACHE_KEY . "{$offset},{$limit}")) {
            $jsonResult = $this->redis->get(self::CACHE_KEY . "{$offset},{$limit}");
            return json_decode($jsonResult, true);
        }
        $result = self::selfGet($offset, $limit);
        $this->redis->set(self::CACHE_KEY . "{$offset},{$limit}", json_encode($result), self::EXPIRE);
        return $result;
    }

    // 新增影片
    public function storeVideo($data)
    {
        try {
            if (! empty($data['id']) and Video::where('id', $data['id'])->exists()) {
                $model = Video::find($data['id']);
                // del tvideo'tag
                self::delVideoCorrespond($model->id, 'tags');
                // del video'actor
                self::delVideoCorrespond($model->id, 'actor');
            } else {
                $model = new Video();
            }
            foreach ($data as $key => $val) {
                $model->{$key} = "{$val}";
            }
            $model->save();
            return $model;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            echo $e->getMessage();
        }
    }

    // 刪除Video tag&& actor關係
    public function delVideoCorrespond(int $videoId, string $type)
    {
        if ($type == 'tags') {
            $model = new TagCorrespond();
        } else {
            $model = new ActorCorrespond();
        }
        $model->where('correspond_type', 'video')->where('correspond_id', $videoId)->delete();
    }

    // 計算Video總數
    public function videoCount()
    {
        return Video::count();
    }

    // 計算總數 存Redis
    public function getVideoCount()
    {
        if ($this->redis->exists(self::COUNT_KEY)) {
            $jsonResult = $this->redis->get(self::COUNT_KEY);
            return json_decode($jsonResult, true);
        }
        $result = (string) self::videoCount();
        $this->redis->set(self::COUNT_KEY, $result, self::COUNT_EXPIRE);
        return $result;
    }

    /**
     * 搜尋影片
     * $compare  = 0  ===>    null
     * $compare  = 1  ===>    >=
     * $compare  = 2  ===>    <=.
     * @param mixed $name
     * @param mixed $compare
     * @param mixed $length
     * @param mixed $offset
     * @param mixed $limit
     */
    public function searchVideo($name, $compare, $length, $offset, $limit)
    {
        # if ($this->redis->exists(self::CACHE_KEY.$name)) {
      #    $jsonResult = $this->redis->get(self::CACHE_KEY.$name);
      #    return json_decode($jsonResult, true);
        # }

        $model = Video::where('name', 'like', "%{$name}%");
        if ($compare > 0 && $length > 0) {
            if ($compare == 1) {
                $model = $model->where('videos.lenght', '>=', $length);
            } else {
                $model = $model->where('videos.lenght', '<=', $length);
            }
        }

        return $model->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();

        // $this->redis->set(self::COUNT_KEY, $model, self::COUNT_EXPIRE);
    }

    // 共用自取
    public function selfGet($offset = 0, $limit = 0)
    {
        return Video::offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // 更新快取
    public function updateCache(): void
    {
        $result = self::selfGet();
        $this->redis->set(self::CACHE_KEY . '0,0', json_encode($result), self::EXPIRE);
    }

    public function getVideosBySuggest(array $suggest, int $page): array
    {
        $result = [];
        $useImageIds = [];
        foreach ($suggest as $value) {
            $limit = $value['proportion'] * Video::PAGE_PER;
            if ($limit < 1) {
                break;
            }

            $imageIds = TagCorrespond::where('correspond_type', Video::class)
                ->where('tag_id', $value['tag_id'])
                ->whereNotIn('correspond_id', $useImageIds)
                ->get()
                ->pluck('correspond_id')
                ->toArray();

            $useImageIds = array_unique(array_merge($imageIds, $useImageIds));

            $models = Video::with([
                'tags',
            ])
                ->whereIn('id', $imageIds)
                ->offset(Video::PAGE_PER * $page)
                ->limit($limit)
                ->get()
                ->toArray();

            $result = array_merge($models, $result);
        }

        return $result;
    }
}
