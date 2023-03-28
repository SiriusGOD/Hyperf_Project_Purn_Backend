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

use App\Model\Product;
use App\Model\Tag;
use App\Model\TagCorrespond;
use App\Model\Image;
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
        $model -> user_id = $data['user_id'];
        $model -> type = $data['type'];
        $model -> correspond_id = $data['correspond_id'];
        $model -> name = $data['name'];
        $model -> expire = $data['expire'];
        $model -> start_time = $data['start_time'];
        $model -> end_time = $data['end_time'];
        $model -> currency = $data['currency'];
        $model -> selling_price = $data['selling_price'];
        $model->save();
    }

    // 新增radis大批匯入的商品ID
    public function insertCache($id)
    {
        $redisKey = self::MULTIPLE_CACHE_KEY . ":" . (int)auth('session')->user()->id;
        $re = $this->redis->lrem($redisKey, 1, (int)$id);
        if($re == 0)$this->redis->lpush($redisKey, $id);
    }

    // 獲取商品列表
    public function getListByKeyword($keyword, $offset, $limit)
    {
        $checkRedisKey = self::CACHE_KEY . ":" . $offset . ":" . $limit . ":" . $keyword;

        if($this->redis->exists($checkRedisKey)){
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $now = Carbon::now()->toDateTimeString();
        if(!empty($keyword)){
            $tagIds = Tag::where('name', 'like', '%' . $keyword . '%')->get()->pluck('id')->toArray();
        }
        
        // image
        $img_query = Product::join('images', 'products.correspond_id', 'images.id')
            ->select('products.id', 'products.name', 'products.start_time', 'products.end_time', 'products.currency', 'products.selling_price', 'images.thumbnail', 'images.like', 'images.description')
            ->where('products.type', '=', Image::class)
            ->where('products.start_time', '<=', $now)
            ->where('products.end_time', '>=', $now)
            ->where('products.expire', Product::EXPIRE['no']);
        if(!empty($tagIds)){
            $img_query = $img_query->join('tag_corresponds', 'images.id', 'tag_corresponds.correspond_id')
                ->where('tag_corresponds.correspond_type', '=', Image::class)
                ->whereIn('tag_corresponds.tag_id',$tagIds);
        }
        if($offset != 0){
            $img_query = $img_query->offset($offset);
        }
        if($limit != 0){
            $img_query = $img_query->limit($limit);
        }
        $img_data = $img_query->get()->toArray();

        // video
        $video_query = Product::join('videos', 'products.correspond_id', 'videos.id')
            ->select('products.id', 'products.name', 'products.start_time', 'products.end_time', 'products.currency', 'products.selling_price', 'videos.m3u8', 'videos.full_m3u8', 'videos.duration', 'videos.cover_thumb', 'videos.like', 'videos.category')
            ->where('products.type', '=', Video::class)
            ->where('products.start_time', '<=', $now)
            ->where('products.end_time', '>=', $now)
            ->where('products.expire', Product::EXPIRE['no']);
        if(!empty($tagIds)){
            $video_query = $video_query->join('tag_corresponds', 'videos.id', 'tag_corresponds.correspond_id')
                ->where('tag_corresponds.correspond_type', '=', Video::class)
                ->whereIn('tag_corresponds.tag_id',$tagIds);
        }
        if($offset != 0){
            $video_query = $video_query->offset($offset);
        }
        if($limit != 0){
            $video_query = $video_query->limit($limit);
        }
        $video_data = $video_query->get()->toArray();

        $data = [
            'image' => $img_data,
            'video' => $video_data
        ];

        $this->redis->set($checkRedisKey, json_encode($data));
        $this->redis->expire($checkRedisKey, self::TTL_30_Min);
        
        return $data;
    }

    // 獲取商品總數 (上架中的)
    public function getCount()
    {
        return Product::where('expire',0)->count();
    }
}
