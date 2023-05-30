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

use App\Model\ImageGroup;
use App\Model\Video;
use App\Service\ClickService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;

#[Crontab(name: 'PopularClickTask', rule: '0 0 * * *', callback: 'execute', memo: '熱門點擊定時任務')]
class PopularClickTask
{
    protected $service;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(ClickService $service, LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
    }

    public function execute()
    {
        $this->logger->info('開始執行熱門點擊定時任務');
        $this->service->calculatePopularClick(ImageGroup::class);
        $this->service->calculatePopularClick(Video::class);
        $this->logger->info('結束執行熱門點擊定時任務');
    }
}
