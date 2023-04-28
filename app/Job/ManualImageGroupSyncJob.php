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
use App\Task\ImageGroupSyncTask;
use Hyperf\AsyncQueue\Job;
use Hyperf\Logger\LoggerFactory;

class ManualImageGroupSyncJob extends Job
{
    /**
     * 任務執行失敗後的重試次數，即最大執行次數為 $maxAttempts+1 次
     */
    protected int $maxAttempts = 2;

    public function __construct()
    {
        // 這裡最好是普通資料，不要使用攜帶 IO 的物件，比如 PDO 物件
    }

    public function handle()
    {
        $logger = make(LoggerFactory::class)->get('job', 'job');
        $logger->info('開始執行手動同步套圖');
        $task = make(ImageGroupSyncTask::class);
        $task->execute();
        $logger->info('結束執行手動同步套圖');
    }
}
