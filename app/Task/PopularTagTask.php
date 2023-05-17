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

use App\Service\TagService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;

#[Crontab(name: 'PopularTagTask', rule: '0 6 * * *', callback: 'execute', memo: '熱門標籤計算定時任務')]
class PopularTagTask
{
    protected $service;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(TagService $service, LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
    }

    public function execute()
    {
        $this->logger->info('開始執行熱門標籤定時任務');
        $this->service->calculateTop6Tag();
        $this->logger->info('結束執行熱門標籤定時任務');
    }
}
