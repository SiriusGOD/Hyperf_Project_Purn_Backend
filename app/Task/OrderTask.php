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

use App\Model\Order;
use App\Service\OrderService;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

#[Crontab(name: 'OrderTask', rule: '*/30 * * * *', callback: 'execute', memo: '訂單狀態變更定時任務')]
class OrderTask
{
    protected Redis $redis;

    protected $service;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(OrderService $service, LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
    }

    public function execute()
    {
        // 獲得 30 分鐘前的時間
        $thirtyMinutesAgo = Carbon::now()->subMinutes(30)->toDateTimeString();

        // 查詢建立時間小於30分鐘前的訂單 狀態變更為付款失敗 31
        $orders = Order::where('status', Order::ORDER_STATUS['create'])->where('created_at', '<', $thirtyMinutesAgo)->select('id')->get();
        if (! empty($orders)) {
            foreach ($orders as $key => $value) {
                $model = Order::findOrFail($value->id);
                $model->status = Order::ORDER_STATUS['failure'];
                $model->save();
            }
        }
    }
}
