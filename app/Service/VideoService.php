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
use App\Model\BuyMemberLevel;
use App\Model\Member;
use App\Model\MemberHasVideo;
use App\Model\MemberLevel;
use App\Model\Order;
use App\Model\Tag;
use App\Model\TagCorrespond;
use App\Model\Video;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class VideoService
{
    public const CACHE_KEY = 'video';

    public const COUNT_KEY = 'video_count';

    public const EXPIRE = 600;

    public const COUNT_EXPIRE = 180;

    protected Redis $redis;

    protected $logger;

    protected $memberHasVideo;

    protected $model;

    public function __construct(Video $video, Redis $redis, LoggerFactory $loggerFactory, MemberHasVideo $memberHasVideo)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
        $this->model = $video;
        $this->memberHasVideo = $memberHasVideo;
    }

    // 取得影片
    public function find(int $id)
    {
        return $this->model->select('id', 'is_free', 'coins', 'title', 'm3u8', 'cover_thumb', 'tags', 'actors')
            ->where('release_time', '<=', Carbon::now()->toDateTimeString())
            ->where('id', $id)
            ->first();
    }

    // 付費影片列表
    public function getPayVideos(?array $tagIds, int $page = 0, int $status = 9, $isFree): Collection
    {
        $query = self::baseVideos($tagIds, $page, $status);
        if ($isFree >= 0) {
            $query = $query->where('is_free', $isFree);
        }
        return $query->get();
    }

    // 影片列表
    public function getVideos(?array $tagIds, int $page = 0, int $status = 9, int $limit = Video::PAGE_PER, array $withoutIds = []): Collection
    {
        $query = self::baseVideos($tagIds, $page, $status, $limit, $withoutIds);
        return $query->orderByDesc('id')->get();
    }

    // 影片
    public function baseVideos(?array $tagIds, int $page = 0, int $status = 9, int $limit = Video::PAGE_PER, array $withoutIds = [])
    {
        $videoIds = [];
        $query = $this->model;
        if (! empty($tagIds)) {
            $videoIds = TagCorrespond::where('correspond_type', 'video')
                ->whereIn('tag_id', $tagIds)
                ->pluck('correspond_id');
        }

        $query = $query->where('release_time', '<=', Carbon::now()->toDateTimeString());
        if ($status != 9) {
            $query->where('status', $status);
        }
        $query = $query->offset($limit * $page)->limit($limit);
        if (! empty($videoIds)) {
            $query = $query->whereIn('id', $videoIds);
        }

        if (! empty($withoutIds)) {
            $query = $query->whereNotIn('id', $withoutIds);
        }

        return $query;
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
            unset($data['_id']);
            unset($data['mod']);
            unset($data['user_id']);
            unset($data['uuid']);
            unset($data['release_at']);
            unset($data['refreshed_at']);
            unset($data['category_id']);
            unset($data['source']);
            unset($data['cover_full']);
            unset($data['sign']);
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
            $model->description ="";
            $model->refreshed_at =date("Y-m-d H:i:s");
            $model->user_id =1;
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
    public function searchVideo(string $title, $compare, int $length, $page, int $limit = Video::PAGE_PER)
    {
        $tagIds = Tag::where('name', 'like', '%' . $title . '%')->get()->pluck('id')->toArray();
        $ids = [];
        if (! empty($tagIds)) {
            $ids = TagCorrespond::where('correspond_type', Video::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id');
        }
        $model = Video::where('title', 'like', "%{$title}%")
            ->where('release_time', '<=', Carbon::now()->toDateTimeString());
        if ($compare > 0 && $length > 0) {
            if ($compare == 1) {
                $model = $model->where('duration', '>=', $length);
            } else {
                $model = $model->where('duration', '<=', $length);
            }
        }

        if (! empty($ids)) {
            $model->whereIn('id', $ids);
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

    public function getVideosBySuggest(array $suggest, int $page, int $inputLimit = Video::PAGE_PER): array
    {
        $result = [];
        $useIds = [];
        foreach ($suggest as $value) {
            $limit = $value['proportion'] * $inputLimit;
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
                ->where('release_time', '<=', Carbon::now()->toDateTimeString())
                ->offset($limit * $page)
                ->limit($limit)
                ->get()
                ->toArray();

            $result = array_merge($models, $result);
        }

        return $result;
    }

    public function adminSearchVideoQuery(array $params): Builder
    {
        $step = Video::PAGE_PER;
        $query = Video::offset(($params['page'] - 1) * $step)->limit($step)
            ->leftJoin('clicks', function ($join) {
                $join->on('videos.id', '=', 'clicks.type_id')->where('clicks.type', Video::class);
            })
            ->leftJoin('likes', function ($join) {
                $join->on('videos.id', '=', 'likes.type_id')->where('likes.type', Video::class);
            })
            ->select('videos.*', Db::raw('clicks.count as click_count'), Db::raw('likes.count as like_count'));
        if (! empty($params['status'])) {
            $query = $query->where('status', $params['status']);
        }

        if (! empty($params['title'])) {
            $query = $query->where('title', 'like', '%' . $params['title'] . '%');
        }

        if (! empty($params['start_duration'])) {
            $query = $query->where('duration', '>=', $params['start_duration']);
        }

        if (! empty($params['end_duration'])) {
            $query = $query->where('duration', '<=', $params['end_duration']);
        }

        if (! empty($params['tag_ids'])) {
            $ids = TagCorrespond::where('correspond_type', Video::class)
                ->whereIn('tag_id', $params['tag_ids'])
                ->get()
                ->pluck('correspond_id')
                ->toArray();

            $query = $query->whereIn('videos.id', $ids);
        }

        return $query;
    }

    public function isPay(int $id, int $memberId): bool
    {
        $member = Member::find($memberId);
        $imageGroup = Video::find($id);

        if ($imageGroup->pay_type > $member->member_level_status or $member->member_level_status == MemberLevel::NO_MEMBER_LEVEL) {
            return $this->orderCheck($id, $memberId);
        }

        $memberLevelType = array_flip(MemberLevel::TYPE_VALUE);
        $buyMemberLevel = BuyMemberLevel::where('member_id', $memberId)->where('member_level_type', $memberLevelType[$member->member_level_status])->first();
        if (empty($buyMemberLevel)) {
            return false;
        }
        $endTime = Carbon::parse($buyMemberLevel->end_time);
        $startTime = Carbon::parse($buyMemberLevel->start_time);
        $diff = $endTime->diffInDays($startTime);
        if (abs($diff) > 1) {
            return true;
        }

        var_dump('all fail');
        return $this->orderCheck($id, $memberId);
    }

    public function getPayVideo(int $id): Video|Collection|\Hyperf\Database\Model\Model|array|null
    {
        return Video::find($id);
    }

    protected function orderCheck(int $id, int $memberId): bool
    {
        return Order::where('orders.user_id', $memberId)
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('products.type', Video::class)
            ->where('products.correspond_id', $id)
            ->where('orders.status', Order::ORDER_STATUS['finish'])
            ->exists();
    }
}
