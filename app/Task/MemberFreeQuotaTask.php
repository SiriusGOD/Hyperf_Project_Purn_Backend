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
namespace App\Task;

use App\Model\Member;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

#[Crontab(name: 'MemberFreeQuotaTask', rule: '00 05 * * *', callback: 'execute', memo: '會員免費觀看次數重置')]
class MemberFreeQuotaTask
{
    protected Redis $redis;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
    }

    public function execute()
    {
        $now = Carbon::now()->toDateTimeString();
        // $yesterday = Carbon::now()->subDay()->toDateString();

        // 撈取所有沒有被禁用的會員
        $members = Member::where('status', '<', Member::STATUS['DISABLE'])->get();
        if (! empty($members)) {
            foreach ($members as $key => $member) {
                if ($member->free_quota != $member->free_quota_limit) {
                    $member->free_quota = $member->free_quota_limit;
                    $member->save();
                }
            }
        }
    }
}
