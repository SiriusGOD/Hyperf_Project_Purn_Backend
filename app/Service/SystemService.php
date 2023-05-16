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

use App\Model\SystemParam;
use Hyperf\Redis\Redis;

class SystemService
{
    public const CACHE_KEY = 'system_params:';
    public const TTL_ONE_DAY = 86400;
    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

     //使用者提現方式
     public function memberWithdrawType()
     {
        $key = self::CACHE_KEY."member_withdraw_type";
        if($this->redis->exists() ){
          $res = $this->redis->get($key);
          //return json_decode($res ,true);
        }
        $result = SystemParam::select("param")
                                ->where('description', 'withdraw_type')
                                ->first();
        $this->redis->set($key, $result->param);
        $this->redis->expire($key, self::TTL_ONE_DAY);
        return json_decode($result->param ,true);
     }
     //使用者提現費率 
     public function memberWithdrawRate()
     {
        $key = self::CACHE_KEY."member_withdraw_rate";
        if($this->redis->exists() ){
          //return $this->redis->get($key);
        }
        $result = SystemParam::select("param")
                                ->where('description', 'member_withdraw')
                                ->first();
        $this->redis->set($key, $result->param);
        $this->redis->expire($key, self::TTL_ONE_DAY);
        return $result->param;
     }

}
