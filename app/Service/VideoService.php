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
use App\Model\Video;
use Hyperf\Redis\Redis;

class VideoService
{
    public const CACHE_KEY = 'video';
    public const COUNT_KEY = 'video_count';
    public const EXPIRE = 600;
    public const COUNT_EXPIRE = 180;

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
      $this->redis = $redis;
    }

    // 取得影片
    public function getVideos($offset=0 ,$limit=0): array
    {
      if ($this->redis->exists(self::CACHE_KEY."$offset,$limit")) {
          $jsonResult = $this->redis->get(self::CACHE_KEY."$offset,$limit");
          return json_decode($jsonResult, true);
      }
      $result = self::selfGet($offset , $limit); 
      $this->redis->set(self::CACHE_KEY."$offset,$limit", json_encode($result),self::EXPIRE);
      return $result;
    }

    // 計算Video總數
    public function videoCount()
    {
      return Video::count();
    }

    // 計算總數 存Redis
    public function getVideoCount(){
      if ($this->redis->exists(self::COUNT_KEY)) {
          $jsonResult = $this->redis->get(self::COUNT_KEY);
          return json_decode($jsonResult, true);
      }
      $result = self::videoCount(); 
      $this->redis->set(self::COUNT_KEY, $result, self::COUNT_EXPIRE);
      return $result;
    }

    /**
     * 搜尋影片
     * $compare  = 0  ===>    null 
     * $compare  = 1  ===>    >= 
     * $compare  = 2  ===>    <= 
     **/
    public function searchVideo($name ,$compare ,$length, $offset, $limit){
      #if ($this->redis->exists(self::CACHE_KEY.$name)) {
      #    $jsonResult = $this->redis->get(self::CACHE_KEY.$name);
      #    return json_decode($jsonResult, true);
      #}

      $model = Product::select("videos.*")
            ->join('videos', 'products.correspond_id', '=', 'videos.id')
            ->where('products.type','video')
            ->where('videos.name','like',"%$name%")
            ->with('video');
      if($compare > 0 && $length >0){
        if($compare == 1){
          $model = $model->where("videos.lenght" , ">=" ,$length); 
        }else{
          $model = $model->where("videos.lenght" , "<=" ,$length); 
        }
      }

      $model=$model->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();

      //$this->redis->set(self::COUNT_KEY, $model, self::COUNT_EXPIRE);
      return $model;
    }

    // 共用自取
    public function selfGet($offset=0 ,$limit=0){
      return Product::select("videos.*")
            ->join('videos', 'products.correspond_id', '=', 'videos.id')
            ->where('type','video')
            ->with('video')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
    }
  
    // 更新快取
    public function updateCache(): void
    {
      $result = self::selfGet(); 
      $this->redis->set(self::CACHE_KEY."0,0", json_encode($result),self::EXPIRE);
    }
}
