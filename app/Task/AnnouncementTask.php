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

use App\Model\Advertisement;
use App\Model\Announcement;
use App\Service\AdvertisementService;
use App\Service\AnnouncementService;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

#[Crontab(name: 'AnnouncementTask', rule: '* * * * *', callback: 'execute', memo: '公告上下架定時任務')]
class AnnouncementTask
{
    protected $service;

    protected $redis;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(AnnouncementService $service, LoggerFactory $loggerFactory, Redis $redis)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
        $this->redis = $redis;
    }

    public function execute()
    {

        $now = Carbon::now()->toDateTimeString();
        if (!$this->redis->exists(AdvertisementService::CACHE_KEY)) {
            $this->logger->info('無公告直接更新');
            return $this->service->updateCache();
        }

        $count = Announcement::where('end_time', '<=', $now)
            ->where('status', Announcement::STATUS['enable'])
            ->count();

        if ($count > 0) {
            $this->logger->info('過期公告更新');
            return $this->service->updateCache();
        }

        $count = Announcement::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('status', Announcement::STATUS['enable'])
            ->count();

        if ($count > 0) {
            $this->logger->info('上架公告更新');
            return $this->service->updateCache();
        }

        return '';
    }
}
