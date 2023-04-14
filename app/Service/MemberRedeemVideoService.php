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
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class MemberRedeemVideoService extends BaseService
{
    public const CACHE_KEY = 'member_redeem_video';

    public const EXPIRE = 3600;

    protected $redis;

    protected $logger;

    protected $memberRedeemVideo;

    protected $videoService;

    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
        MemberRedeemVideo $memberRedeemvideo
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->memberRedeemVideo = $memberRedeemvideo;
    }

    public function checkMemeberUsed(int $memberId, int $videoId)
    {
        return $this->memberRedeemVideo
            ->where('video_id', $videoId)
            ->where('member_id', $memberId)->exists();
    }
}
