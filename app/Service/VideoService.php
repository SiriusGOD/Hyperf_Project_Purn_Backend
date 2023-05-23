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

use App\Constants\Constants;
use App\Model\Actor;
use App\Model\ActorCorrespond;
use App\Model\BuyMemberLevel;
use App\Model\ImageGroup;
use App\Model\Member;
use App\Model\MemberHasVideo;
use App\Model\MemberLevel;
use App\Model\Order;
use App\Model\Product;
use App\Model\Tag;
use App\Model\TagCorrespond;
use App\Model\Video;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use App\Util\Calc;
class VideoService
{
    public const CACHE_KEY = 'video';

    public const COUNT_KEY = 'video_count';

    public const EXPIRE = 600;

    public const COUNT_EXPIRE = 180;

    protected Redis $redis;

    protected $logger;

    protected $memberHasVideo;

    protected $productService;

    protected $model;

    public function __construct(ProductService $productService, Video $video, Redis $redis, LoggerFactory $loggerFactory, MemberHasVideo $memberHasVideo)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('video');
        $this->model = $video;
        $this->memberHasVideo = $memberHasVideo;
        $this->productService = $productService;
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
        $query = $this->model->where('cover_height', '>', 0);
        if (! empty($tagIds)) {
            $videoIds = TagCorrespond::where('correspond_type', Video::class)
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

        $hideIds = ReportService::getHideIds(Video::class);

        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(Video::class);

        if (!empty($enableIds)) {
            $query = $query->whereIn('id', $enableIds);
        }

        if (! empty($hideIds)) {
            $withoutIds = array_merge($withoutIds, $hideIds);
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
            $videoIds = ActorCorrespond::where('correspond_type', Video::class)
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
            unset($data['user_id'], $data['uuid'], $data['release_at'], $data['refreshed_at']);

            if (! empty($data['_id']) and Video::where('_id', $data['_id'])->exists()) {
                $model = Video::withTrashed()->where('_id',$data['_id'])->first();
                // del tvideo'tag
                self::delVideoCorrespond($model->id, 'tags');
                // del video'actor
                self::delVideoCorrespond($model->id, 'actor');
            } else {
                $model = new Video();
            }
            
            $cover = env("COVER_URL").$data['cover_full'];
            $imgSize = Calc::imgSize($cover); 
            $data['cover_witdh'] = isset($imgSize['width']) ?$imgSize['width'] :0;
            $data['cover_height'] = isset($imgSize['heigh']) ?$imgSize['heigh'] :0;
            $this->logger->info("video_info ". var_export($data, true));
            foreach ($data as $key => $val) {
                $model->{$key} = !empty($val)? "{$val}" : 0;
            }
            $model->description = '';
            $model->refreshed_at = date('Y-m-d H:i:s');
            $model->user_id = 1;
            $model->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
            if ($model->save()) {
                $data['id'] = null;
                $data['type'] = Product::TYPE_CORRESPOND_LIST['video'];
                $data['correspond_id'] = $model->id;
                $data['name'] = $model->title;
                $data['user_id'] = 1;
                $data['expire'] = Product::EXPIRE['no'];
                $data['start_time'] = date('Y-m-d H:i:s');
                $data['end_time'] = date('Y-m-d H:i:s', strtotime('+10 years'));
                $data['currency'] = 'COIN';
                $data['diamond_price'] = 1;
                $data['selling_price'] = empty($model->coin) ? 0 : $model->coin;
                $this->productService->store($data);
            }
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
        $model->where('correspond_type', Video::class)->where('correspond_id', $videoId)->delete();
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
    public function searchVideo(string $title, $compare, int $length, $page, int $limit, ?int $sortBy = null, ?int $isAsc = null)
    {
        $tagIds = Tag::where('name', 'like', '%' . $title . '%')->get()->pluck('id')->toArray();
        $ids = [];
        if (! empty($tagIds)) {
            $ids = TagCorrespond::where('correspond_type', Video::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id')
                ->toArray();
        }

        $actorIds = Actor::where('name', 'like', '%' . $title . '%')->get()->pluck('id')->toArray();
        if (!empty($actorIds)) {
            $result = ActorCorrespond::where('correspond_type', Video::class)
                ->whereIn('actor_id', $actorIds)
                ->get()
                ->pluck('correspond_id')
                ->toArray();
            $ids = array_merge($ids, $result);
        }
        $model = Video::where('title', 'like', "%{$title}%")->where('cover_height', '>', 0)
            ->where('release_time', '<=', Carbon::now()->toDateTimeString())
            ->offset($page * $limit)
            ->limit($limit);
        if ($compare > 0 && $length > 0) {
            if ($compare == 1) {
                $model = $model->where('duration', '>=', $length);
            } else {
                $model = $model->where('duration', '<=', $length);
            }
        }

        if (! empty($sortBy) and $sortBy == Constants::SORT_BY['click']) {
            if ($isAsc == 1) {
                $model = $model->orderBy('total_click');
            } else {
                $model = $model->orderByDesc('total_click');
            }
        } elseif(! empty($sortBy) and $sortBy == Constants::SORT_BY['created_time']) {
            if ($isAsc == 1) {
                $model = $model->orderBy('id');
            } else {
                $model = $model->orderByDesc('id');
            }
        }

        $models = $model->offset($limit * $page)->limit($limit)->get();
        $ids = array_merge($ids, $models->pluck('id')->toArray());

        $model = Video::where('cover_height', '>', 0)
            ->where('release_time', '<=', Carbon::now()->toDateTimeString());

        $hideIds = ReportService::getHideIds(Video::class);
        if(! empty($hideIds)) {
            $model = $model->whereNotIn('id', $hideIds);
        }

        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(Video::class);
        if (! empty($enableIds)) {
            $diff = \Hyperf\Collection\collect($enableIds)->intersect(\Hyperf\Collection\collect($ids));
            $model = $model->whereIn('id', $diff);
        } else {
            $model = $model->whereIn('id', $ids);
        }

        if (! empty($sortBy) and $sortBy == Constants::SORT_BY['click']) {
            if ($isAsc == 1) {
                $model = $model->orderBy('total_click');
            } else {
                $model = $model->orderByDesc('total_click');
            }
        } elseif(! empty($sortBy) and $sortBy == Constants::SORT_BY['created_time']) {
            if ($isAsc == 1) {
                $model = $model->orderBy('id');
            } else {
                $model = $model->orderByDesc('id');
            }
        }

        return $model->with('tags')->get();
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

    public function getVideosBySuggest(array $suggest, int $page, int $inputLimit, array $withoutIds = []): array
    {
        $result = [];
        $useIds = [];
        $hideIds = ReportService::getHideIds(Video::class);
        $hideIds = array_merge($hideIds, $withoutIds);
        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(Video::class);
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

            $query = Video::with([
                'tags',
            ])
                ->whereIn('id', $ids)
                ->where('release_time', '<=', Carbon::now()->toDateTimeString())
                ->where('cover_height', '>', 0)
                ->offset($limit * $page)
                ->limit($limit);

            if(! empty($hideIds)) {
                $query = $query->whereNotIn('id', $hideIds);
            }

            if (! empty($enableIds)) {
                $query = $query->whereIn('id', $enableIds);
            }

            $models = $query->get()->toArray();

            $result = array_merge($models, $result);
        }

        return $result;
    }

    public function adminSearchVideoQuery(array $params): Builder
    {
        $step = Video::PAGE_PER;
        $query = Video::withTrashed()->offset(($params['page'] - 1) * $step)->limit($step)
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

    public function isPay(int $id, int $memberId, $ip = '127.0.0.1'): bool
    {
        $member = Member::find($memberId);
        $video = Video::find($id);
        $product = Product::where('expire', Product::EXPIRE['no'])
                ->where('type', Video::class)
                ->where('correspond_id', $id)
                ->first();

        // 判定影片等級與會員等級
        if($video->is_free <= $member->member_level_status){
            $data['user_id'] = $memberId;
            $data['prod_id'] = $product -> id;
            $data['payment_type'] = 0;
            $data['pay_proxy'] = 'online';
            $data['ip'] = $ip;
            $data['product'] = $product->toArray();
            $data['user'] = $member->toArray();
            $data['oauth_type'] = $member -> device ?? '';

            switch ($member->member_level_status) {
                // 免費會員
                case MemberLevel::NO_MEMBER_LEVEL:
                    // 確認是否購買過
                    $is_buy = $this->orderCheck($id, $memberId);
                    $service = make(OrderService::class);
                    if(!$is_buy){
                        // 未購買過 -> 使用免費次數購買
                        if($member->free_quota > 0){
                            // 購買
                            $data['pay_method'] = 'free_quota';
                            // 建立訂單
                            $result = $service->createOrder($data);
                            if ($result) {
                                // 扣免費觀看次數
                                $quota = $member->free_quota - Product::QUOTA;
                                $member->free_quota = $quota;
                                $re = $member->save();
                                $pay_amount = Product::QUOTA;

                                // 變更訂單狀態為已完成
                                if ($re) {
                                    $order = Order::where('order_number', $result)->first();
                                    $order->pay_amount = $pay_amount;
                                    $order->status = Order::ORDER_STATUS['finish'];
                                    $order->save();
                                }
                            }
                        }else{
                            // 次數不足
                            return false;
                        }
                    }
                    // 刪除會員快取
                    $service -> delMemberRedis($memberId);
                    return true;
                    break;
                // Vip會員
                case MemberLevel::TYPE_VALUE['vip']:
                    // 確認是否購買過
                    $is_buy = $this->orderCheck($id, $memberId);
                    $service = make(OrderService::class);
                    if(!$is_buy){
                        // 未購買過 -> 使用Vip次數購買
                        if($member->vip_quota > 0){
                            // 購買
                            $data['pay_method'] = 'vip_quota';
                            // 建立訂單
                            $result = $service->createOrder($data);
                            if ($result) {
                                // 扣Vip觀看次數
                                $quota = $member->vip_quota - Product::QUOTA;
                                $member->vip_quota = $quota;
                                $re = $member->save();
                                $pay_amount = Product::QUOTA;

                                // Vip次數歸0時，判斷是否要降等!!!!
                                if ($quota == 0) {
                                    $service->memberLevelDown($memberId);
                                }

                                // 變更訂單狀態為已完成
                                if ($re) {
                                    $order = Order::where('order_number', $result)->first();
                                    $order->pay_amount = $pay_amount;
                                    $order->status = Order::ORDER_STATUS['finish'];
                                    $order->save();
                                }
                            }
                        }else if($member->vip_quota === 0){
                            // 次數不足
                            return false;
                        }else{
                            // 次數為Null -> 可以直接看
                            return true;
                        }
                    }
                    // 刪除會員快取
                    $service -> delMemberRedis($memberId);
                    return true;
                    break;

                // 鑽石會員
                case MemberLevel::TYPE_VALUE['diamond']:
                    // 確認是否購買過
                    $is_buy = $this->orderCheck($id, $memberId);
                    $service = make(OrderService::class);
                    if(!$is_buy){
                        // 未購買過 -> 使用鑽石次數購買
                        if($member->diamond_quota > 0){
                            // 購買
                            $data['pay_method'] = 'diamond_quota';
                            // 建立訂單
                            $result = $service->createOrder($data);
                            if ($result) {
                                // 扣鑽石觀看次數
                                $quota = $member->diamond_quota - Product::QUOTA;
                                $member->diamond_quota = $quota;
                                $re = $member->save();
                                $pay_amount = Product::QUOTA;

                                // 鑽石次數歸0時，判斷是否要降等!!!!
                                if ($quota == 0) {
                                    $service->memberLevelDown($memberId);
                                }

                                // 變更訂單狀態為已完成
                                if ($re) {
                                    $order = Order::where('order_number', $result)->first();
                                    $order->pay_amount = $pay_amount;
                                    $order->status = Order::ORDER_STATUS['finish'];
                                    $order->save();
                                }
                            }
                        }else if($member->diamond_quota === 0){
                            // 次數不足
                            return false;
                        }else{
                            // 次數為Null -> 可以直接看
                            return true;
                        }
                    }
                    // 刪除會員快取
                    $service -> delMemberRedis($memberId);
                    return true;
                    break;
            }
        }else{
            return $this->orderCheck($id, $memberId);
        }

    }

    public function getPayVideo(int $id): Video|Collection|\Hyperf\Database\Model\Model|array|null
    {
        return Video::find($id);
    }

    public function getVideosByHotOrder(int $page, int $limit): array
    {
        $hideIds = ReportService::getHideIds(Video::class);
        $query = Video::with('tags')
            ->where('hot_order', '>=', 1)
            ->where('cover_height', '>', 0)
            ->orderBy('hot_order')
            ->offset($page * $limit)
            ->limit($limit);

        if (! empty($hideIds)) {
            $query = $query->whereNotIn('id', $hideIds);
        }

        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(Video::class);
        if (!empty($enableIds)) {
            $query = $query->whereIn('id', $enableIds);
        }

        return $query->get()->toArray();
    }

    protected function orderCheck(int $id, int $memberId): bool
    {
        $order = Order::where('orders.user_id', $memberId)
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->where('products.type', Video::class)
                ->where('products.correspond_id', $id)
                ->where('orders.status', Order::ORDER_STATUS['finish'])->select('orders.currency', 'orders.created_at')->orderBy('orders.created_at', 'desc')->first();
        if(empty($order))return false;
        
        // 用免費次數購買的免費商品 過隔天就不顯示在已購買項目中
        if($order -> currency == Order::PAY_CURRENCY['free_quota']){
            $date1 = Carbon::parse($order -> created_at);
            $date2 = Carbon::now();
            $diff = $date1->diffInDays($date2);
            if(abs($diff) > 0)return false;
        }
        return true;
    }
}
