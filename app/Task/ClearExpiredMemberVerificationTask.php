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

use App\Model\MemberVerification;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;

#[Crontab(name: 'ClearExpiredMemberVerificationTask', rule: '* * * * *', callback: 'execute', memo: '清除過期驗證碼定時任務')]
class ClearExpiredMemberVerificationTask
{
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
    }

    public function execute()
    {
        $now = Carbon::now()->toDateTimeString();
        $this->logger->info('開始執行清除過期驗證碼定時任務');
        MemberVerification::where('expired_at', '<=', $now)->delete();
        $this->logger->info('結束執行清除過期驗證碼定時任務');
    }
}
