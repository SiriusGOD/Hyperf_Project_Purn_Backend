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

use App\Model\MemberRedeemVideo;
use App\Model\MemberRedeem;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class MemberRedeemVideoService extends BaseService
{
    public const CACHE_KEY = 'member_redeem_video';
    public const EXPIRE = 3600;

    protected $redis;
    protected $logger;
    protected $memberRedeem;
    protected $videoService;

    public function __construct(
        Redis $redis, 
        LoggerFactory $loggerFactory, 
        MemberRedeem $memberRedeem
    )
    {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->memberRedeem = $memberRedeem;
    }
}
