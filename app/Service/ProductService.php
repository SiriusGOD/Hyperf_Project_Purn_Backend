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

use App\Model\Image;
use App\Model\PayCorrespond;
use App\Model\Product;
use App\Model\Tag;
use App\Model\Video;
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class ProductService
{
    public const CACHE_KEY = 'product';

    public const MULTIPLE_CACHE_KEY = 'multiple_cache';

    public const TTL_30_Min = 1800;

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 更新快取
    public function updateCache(): void
    {
        $now = Carbon::now()->toDateTimeString();
        $result = Product::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('expire', Product::EXPIRE['no'])
            ->get()
            ->toArray();

        $this->redis->set(self::CACHE_KEY, json_encode($result));
    }

    // 新增或更新
    public function store(array $data)
    {
        $model = Product::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->type = $data['type'];
        if ($data['type'] == Product::TYPE_LIST[0] || $data['type'] == Product::TYPE_LIST[1]) {
            $model->diamond_price = Product::DIAMOND_PRICE;
        }
        $model->correspond_id = $data['correspond_id'];
        $model->name = $data['name'];
        $model->expire = $data['expire'];
        $model->start_time = $data['start_time'];
        $model->end_time = $data['end_time'];
        $model->currency = $data['currency'];
        $model->selling_price = $data['selling_price'];
        $model->save();

        if (! empty($data['pay_groups'])) {
            // 新增或更新支付方式
            if (! PayCorrespond::where('product_id', $model->id)->whereNull('deleted_at')->exists()) {
                // 新增
                foreach ($data['pay_groups'] as $key => $value) {
                    $payment = new PayCorrespond();
                    $payment->product_id = $model->id;
                    $payment->pay_id = $value;
                    $payment->save();
                }
            } else {
                // 更新
                // 撈出目前有設定的支付
                $pays = PayCorrespond::where('product_id', $model->id)->whereNull('deleted_at')->get()->pluck('pay_id')->toArray();

                // 比對要刪除的支付
                $deletes = array_diff($pays, $data['pay_groups']);
                foreach ($deletes as $key => $value) {
                    $payment = PayCorrespond::where('product_id', $model->id)->where('pay_id', $value)->first();
                    $payment->delete();
                }

                // 比對要新增的支付
                $adds = array_diff($data['pay_groups'], $pays);
                foreach ($adds as $key => $value) {
                    $payment = new PayCorrespond();
                    $payment->product_id = $model->id;
                    $payment->pay_id = $value;
                    $payment->save();
                }
            }
        }
    }

    // 新增radis大批匯入的商品ID
    public function insertCache($id)
    {
        $redisKey = self::MULTIPLE_CACHE_KEY . ':' . (int) auth('session')->user()->id;
        $re = $this->redis->lrem($redisKey, 1, (int) $id);
        if ($re == 0) {
            $this->redis->lpush($redisKey, $id);
        }
    }

    // 獲取商品列表
    public function getListByKeyword($keyword, $offset, $limit)
    {
        $checkRedisKey = self::CACHE_KEY . ':' . $offset . ':' . $limit . ':' . $keyword;

        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $now = Carbon::now()->toDateTimeString();
        if (! empty($keyword)) {
            $tagIds = Tag::where('name', 'like', '%' . $keyword . '%')->get()->pluck('id')->toArray();
        }
        // image
        $img_query = Product::join('images', 'products.correspond_id', 'images.id')
            ->select('products.id', 'products.name', 'products.start_time', 'products.end_time', 'products.currency', 'products.selling_price', 'images.thumbnail', 'images.like', 'images.description')
            ->where('products.type', '=', Image::class)
            ->where('products.start_time', '<=', $now)
            ->where('products.end_time', '>=', $now)
            ->where('products.expire', Product::EXPIRE['no']);

        if (! empty($tagIds)) {
            $img_query = $img_query->leftjoin('tag_corresponds', 'images.id', 'tag_corresponds.correspond_id')
                ->where('tag_corresponds.correspond_type', '=', Image::class)
                ->whereIn('tag_corresponds.tag_id', $tagIds)
                ->orwhere('products.name', 'like', '%' . $keyword . '%');
        } elseif (! empty($keyword)) {
            $img_query = $img_query->where('products.name', 'like', '%' . $keyword . '%');
        }
        if ($offset != 0) {
            $img_query = $img_query->offset($offset);
        }
        if ($limit != 0) {
            $img_query = $img_query->limit($limit);
        }
        $img_data = $img_query->get()->toArray();
        foreach ($img_data as $key => $value) {
            $img_data[$key]['selling_price'] = (float) $value['selling_price'];
        }

        // video
        $video_query = Product::join('videos', 'products.correspond_id', 'videos.id')
            ->select('products.id', 'products.name', 'products.start_time', 'products.end_time', 'products.currency', 'products.selling_price', 'videos.m3u8', 'videos.full_m3u8', 'videos.duration', 'videos.cover_thumb', 'videos.likes', 'videos.category')
            ->where('products.type', '=', Video::class)
            ->where('products.start_time', '<=', $now)
            ->where('products.end_time', '>=', $now)
            ->where('products.expire', Product::EXPIRE['no']);

        if (! empty($tagIds)) {
            $video_query = $video_query->leftjoin('tag_corresponds', 'videos.id', 'tag_corresponds.correspond_id')
                ->where('tag_corresponds.correspond_type', '=', Video::class)
                ->whereIn('tag_corresponds.tag_id', $tagIds)
                ->orWhere('products.name', 'like', '%' . $keyword . '%');
        } elseif (! empty($keyword)) {
            $video_query = $video_query->where('products.name', 'like', '%' . $keyword . '%');
        }
        if ($offset != 0) {
            $video_query = $video_query->offset($offset);
        }
        if ($limit != 0) {
            $video_query = $video_query->limit($limit);
        }
        $video_data = $video_query->get()->toArray();

        foreach ($video_data as $key => $value) {
            $video_data[$key]['selling_price'] = (float) $value['selling_price'];
        }

        $data = [
            'image' => $img_data,
            'video' => $video_data,
        ];

        $this->redis->set($checkRedisKey, json_encode($data));
        $this->redis->expire($checkRedisKey, self::TTL_30_Min);

        return $data;
    }

    // 獲取商品總數 (上架中的)
    public function getCount($keyword)
    {
        if (! empty($keyword)) {
            $tagIds = Tag::where('name', 'like', '%' . $keyword . '%')->get()->pluck('id')->toArray();
        }

        $query = Product::where('expire', 0);

        if (! empty($tagIds)) {
            $query = Product::join('tag_corresponds', function ($join) {
                $join->on('products.correspond_id', '=', 'tag_corresponds.correspond_id')
                    ->on('products.type', '=', 'tag_corresponds.correspond_type');
            })
                ->whereIn('tag_corresponds.tag_id', $tagIds)
                ->orWhere('products.name', 'like', '%' . $keyword . '%');
        } elseif (! empty($keyword)) {
            $query = $query->where('products.name', 'like', '%' . $keyword . '%');
        }
        return $query->count();
    }
}
