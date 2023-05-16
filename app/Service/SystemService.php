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

     //使用者提現費率 
     public function memberWithdraw()
     {
        $result = SystemParam::select("param")
                                ->where('description', 'member_withdraw')
                                ->first();
        $this->redis->set(self::CACHE_KEY."member_withdraw", $result->param);
        $this->redis->expire(self::CACHE_KEY, self::TTL_ONE_DAY);
        return $result->param;
     }

}
