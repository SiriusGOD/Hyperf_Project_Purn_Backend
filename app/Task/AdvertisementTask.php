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
use App\Service\AdvertisementService;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

/**
 * @Crontab(name="AdvertisementTask", rule="* * * * *", callback="execute", memo="廣告上下架定時任務")
 */
class AdvertisementTask
{
    protected Redis $redis;

    protected $service;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(AdvertisementService $service, LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
    }

    public function execute()
    {
        $now = Carbon::now()->toDateTimeString();
        $models = Advertisement::where('end_time', '<=', $now)
            ->where('expire', Advertisement::EXPIRE['no'])
            ->get();

        if (count($models) == 0) {
            return;
        }

        $this->logger->info('有廣告過期');
        foreach ($models as $model) {
            $model->expire = Advertisement::EXPIRE['yes'];
            $model->save();
            $this->logger->info('廣告 id : '.$model->id.' 過期');
        }

        $this->service->updateCache();

        $this->logger->info('更新廣告完成');
    }
}
