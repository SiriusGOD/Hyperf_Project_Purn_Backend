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
use Hyperf\AsyncQueue\Job;
use Hyperf\Logger\LoggerFactory;

class EmailVerificationJob extends Job
{
    public string $address;

    public string $content;

    /**
     * 任務執行失敗後的重試次數，即最大執行次數為 $maxAttempts+1 次
     */
    protected int $maxAttempts = 2;

    public function __construct(string $address, string $content)
    {
        // 這裡最好是普通資料，不要使用攜帶 IO 的物件，比如 PDO 物件
        $this->address = $address;
        $this->content = $content;
    }

    public function handle()
    {
        $logger = make(LoggerFactory::class)->get('job', 'job');
        $service = make(MailService::class);
        $logger->info('寄送 mail 至 ' . $this->address);
        $result = $service->send($this->address, $this->content);
        $msg = '寄送 mail 至 ' . $this->address . ' 失敗';
        if ($result) {
            $msg = '寄送 mail 至 ' . $this->address . ' 成功';
        }
        $logger->info($msg);
    }
}
