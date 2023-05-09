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
namespace App\Job;

use App\Service\MailService;
use App\Service\ReportService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Logger\LoggerFactory;

class MemberHideModelJob extends Job
{
    public int $memberId;
    /**
     * 任務執行失敗後的重試次數，即最大執行次數為 $maxAttempts+1 次
     */
    protected int $maxAttempts = 2;

    public function __construct(int $memberId)
    {
        // 這裡最好是普通資料，不要使用攜帶 IO 的物件，比如 PDO 物件
        $this->memberId = $memberId;
    }

    public function handle()
    {
        $logger = make(LoggerFactory::class)->get('job', 'job');
        $service = make(ReportService::class);
        $logger->info('更新 member 隱藏或檢舉 : ' . $this->memberId);
        $service->updateMemberCache($this->memberId);
        $logger->info('更新 member 隱藏或檢舉結束 : ' . $this->memberId);
    }
}
