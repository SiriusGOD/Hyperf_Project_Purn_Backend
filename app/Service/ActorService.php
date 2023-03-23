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

use App\Model\Actor;
use App\Model\ActorCorrespond;
use Hyperf\Redis\Redis;

class ActorService
{
    public const CACHE_KEY = 'actor';
    public const COUNT_KEY = 'actor_count';
    public const EXPIRE = 600;
    public const COUNT_EXPIRE = 180;

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }
    //影片演員關聯
    public function videoCorrespondActor(array $data ,int $videoId){
      if($data['tags']!=""){
        $tags = explode(",",$data['tags']);
        foreach($tags as $v){
          $d['name'] = $v; 
          $d['user_id'] = 1; 
          $d['sex'] = 0; 
          $actor = self::storeActorByName($d);
          self::createActorRelationship("video", $videoId, $actor->id);
        }
      }
    }

    // 取得演員
    public function getActors($offset=0 ,$limit=0): array
    {
        if ($this->redis->exists(self::CACHE_KEY."$offset,$limit")) {
            $jsonResult = $this->redis->get(self::CACHE_KEY."$offset,$limit");
            return json_decode($jsonResult, true);
        }
        $result = self::selfGet($offset , $limit); 
        $this->redis->set(self::CACHE_KEY."$offset,$limit", json_encode($result),self::EXPIRE);
        return $result;
    }

    // 計算總數
    public function getCount(){
        return Actor::count();
    }

    // 計算總數 存Redis
    public function getActorCount(){
        if ($this->redis->exists(self::COUNT_KEY)) {
            $jsonResult = $this->redis->get(self::COUNT_KEY);
            return json_decode($jsonResult, true);
        }
        $result = self::getCount(); 
        $this->redis->set(self::COUNT_KEY, $result, self::COUNT_EXPIRE);
        return $result;
    }

    // 共用自取
    public function selfGet($offset=0 ,$limit=0){
        return Actor::select("id","sex","name",'created_at')
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

    // 新增或更新演員
    public function storeActor(array $data): void
    {
        $model = Actor::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->name = $data['name'];
        $model->sex = $data['sex'];
        $model->save();
        $this->updateCache();
    }

    // 新增或更新演員
    public function storeActorByName(array $data)
    {
      if(Actor::where('name', $data['name'])->exists()) {
          $model = Actor::where("name",$data['name'])->first();
      }else{
          $model = new Actor();
      }
      $model->user_id = $data['user_id'];
      $model->name = $data['name'];
      $model->sex = $data['sex'];
      $model->save();
      return $model;
    }

    //新熷 演員關係
    public function createActorRelationship(string $className, int $classId, int $actorId) : void
    {
        $model = new ActorCorrespond();
        $model->correspond_type = $className;
        $model->correspond_id = $classId;
        $model->actor_id = $actorId;
        $model->save();
    }

}

