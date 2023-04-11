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
use App\Model\MemberHasVideo;
use App\Model\TagCorrespond;
use App\Model\Video;
use Hyperf\Database\Model\Collection;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class VideoService
{
    public const CACHE_KEY = 'video';

    public const COUNT_KEY = 'video_count';

    public const EXPIRE = 600;

    public const COUNT_EXPIRE = 180;

    protected Redis $redis;

    public function __construct(Video $video, Redis $redis, LoggerFactory $loggerFactory, MemberHasVideo $memberHasVideo)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
        $this->model = $video;
        $this->memberHasVideo = $memberHasVideo;
    }

    // 我收藏的影片
    public function myStageVideo(int $memberId, int $page = 0)
    {
        $model = $this->memberHasVideo->where('member_id', $memberId)->offset(MemberHasVideo::PAGE_PER * $page)->limit(MemberHasVideo::PAGE_PER);
        return $model->get();
    }

    // 收藏影片
    public function storeStageVideo(int $videoId, int $memberId)
    {
        if (! $this->memberHasVideo->where('member_id', $memberId)->where('video_id', $videoId)->exists()) {
            $model = $this->memberHasVideo;
            $model->video_id = $videoId;
            $model->member_id = $memberId;
            if ($model->save()) {
                return true;
            }
            $this->logger->error('error');
            return false;
        }
        return true;
    }

    // 收藏影片
    public function delStageVideo(array $ids)
    {
        if ($this->memberHasVideo->whereIn('id', $ids)->delete()) {
            $this->logger->info('success');
            return true;
        }
        return false;
    }

    // 取得影片
    public function find(int $id)
    {
        return $this->model->select('id', 'title', 'm3u8', 'cover_thumb', 'tags', 'actors')->where('id', $id)->first();
    }

    // 影片列表
    public function getVideos(?array $tagIds, int $page): Collection
    {
        $videoIds = [];
        $query = $this->model;
        if (! empty($tagIds)) {
            $videoIds = TagCorrespond::where('correspond_type', 'video')
                ->whereIn('tag_id', $tagIds)
                ->pluck('correspond_id');
        }
        // if(!empty($tagIds)){
        //  $query = $query->with([
        //      'tags',
        //  ]);
        // }
        $query = $query->offset(Video::PAGE_PER * $page)->limit(Video::PAGE_PER);
        if (! empty($videoIds)) {
            $query = $query->whereIn('id', $videoIds);
        }
        return $query->get();
    }

    // 影片列表
    public function getVideosByCorresponds(?array $corresponds, int $page): Collection
    {
        $videoIds = [];
        $query = $this->model;
        if (! empty($corresponds)) {
            $videoIds = ActorCorrespond::where('correspond_type', 'video')
                ->whereIn('actor_id', $corresponds['actors'])
                ->pluck('correspond_id');
        }
        $query = $query->offset(Video::PAGE_PER * $page)->limit(Video::PAGE_PER);
        if (! empty($actorCorr)) {
            $query = $query->whereIn('id', $videoIds);
        }
        return $query->get();
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
     * @param mixed $page
     */
    public function searchVideo(string $title, $compare, int $length, $page)
    {
        # if ($this->redis->exists(self::CACHE_KEY.$name)) {
        #  $jsonResult = $this->redis->get(self::CACHE_KEY.$name);
        #  return json_decode($jsonResult, true);
        # }
        $model = Video::where('title', 'like', "%{$title}%");
        if ($compare > 0 && $length > 0) {
            if ($compare == 1) {
                $model = $model->where('duration', '>=', $length);
            } else {
                $model = $model->where('duration', '<=', $length);
            }
        }
        // $this->redis->set(self::COUNT_KEY, $model, self::COUNT_EXPIRE);
        return $model->offset(Video::PAGE_PER * $page)->limit(Video::PAGE_PER)->get();
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
        $useIds = [];
        foreach ($suggest as $value) {
            $limit = $value['proportion'] * Video::PAGE_PER;
            if ($limit < 1) {
                break;
            }

            $ids = TagCorrespond::where('correspond_type', Video::class)
                ->where('tag_id', $value['tag_id'])
                ->whereNotIn('correspond_id', $useIds)
                ->get()
                ->pluck('correspond_id')
                ->toArray();

            $useIds = array_unique(array_merge($ids, $useIds));

            $models = Video::with([
                'tags',
            ])
                ->whereIn('id', $ids)
                ->offset($limit * $page)
                ->limit($limit)
                ->get()
                ->toArray();

            $result = array_merge($models, $result);
        }

        return $result;
    }
}
