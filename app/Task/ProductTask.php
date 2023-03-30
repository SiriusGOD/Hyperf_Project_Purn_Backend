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

use App\Model\Product;
use App\Service\ProductService;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

#[Crontab(name: 'ProductTask', rule: '* * * * *', callback: 'execute', memo: '廣告上下架定時任務')]
class ProductTask
{
    protected Redis $redis;

    protected $service;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(ProductService $service, LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
    }

    public function execute()
    {
        $now = Carbon::now()->toDateTimeString();
        $models = Product::where('end_time', '<=', $now)->where('expire', Product::EXPIRE['no'])->get();
        if (count($models) == 0) {
            return;
        }
        $this->logger->info('有商品過期');
        foreach ($models as $model) {
            $model->expire = Product::EXPIRE['yes'];
            $model->save();
            $this->logger->info('商品 id : ' . $model->id . ' 過期');
        }
        $this->service->updateCache();
        $this->logger->info('更新商品完成');
    }
}
