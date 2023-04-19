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

use App\Model\MemberFollow;
use App\Model\MemberVerification;
use App\Model\Role;
use App\Model\User;
use Carbon\Carbon;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class MemberFollowService
{

    public const CACHE_KEY = 'member:token:';

    public const DEVICE_CACHE_KEY = 'member:device:';

    public const EXPIRE_VERIFICATION_MINUTE = 10;

    protected Redis $redis;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }
    //追蹤多個tags
    public function addTagsFlower(string $tag, int $userId ,array $follow_ids)
    {
      foreach($follow_ids as $follow_id){
        $model = MemberFollow::where('member_id', $userId)
            ->where('correspond_type', MemberFollow::TYPE_CORRESPOND_LIST[$tag])
            ->where('correspond_id', $follow_id)
            ->whereNull('deleted_at')
            ->exists();

        if (!empty($model)) {
            $model = new MemberFollow();
            $model->member_id = $userId;
            $model->correspond_type = MemberFollow::TYPE_CORRESPOND_LIST[$tag];
            $model->correspond_id = $follow_id;
            $model->save();
        }
      }
      return true;
    }
}
