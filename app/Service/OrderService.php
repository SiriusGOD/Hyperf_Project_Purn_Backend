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
namespace App\Service;

use App\Model\Order;
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class OrderService
{
    public const CACHE_KEY = 'order';

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 取得訂單
    public function searchOrders($order_number, $order_status, $page = 1): object
    {
        // 顯示幾筆
        $step = Order::PAGE_PER;
        $query = Order::join('users','orders.user_id','users.id')
            ->select('orders.*','users.name');
        if(!empty($order_number)){
            $query = $query -> where('orders.order_number', '=', $order_number);
        }else if(!empty($order_status)){
            $query = $query -> where('orders.status', '=', $order_status);
        }
        $query = $query ->offset(($page - 1) * $step)
            ->orderByDesc('orders.id')
            ->limit($step);
        $orders = $query->get();
        return $orders;
    }

    // 取得訂單
    public function getOrdersCount($order_number, $order_status): int
    {
        $query = Order::select('*');
        if(!empty($order_number)){
            $query = $query -> where('orders.order_number', '=', $order_number);
        }else if(!empty($order_status)){
            $query = $query -> where('orders.status', '=', $order_status);
        }
        $total = $query->count();
        return $total;
    }


    // 更新快取
    public function updateCache(): void
    {
        $now = Carbon::now()->toDateTimeString();
        $result = Order::where('created_at', '<=', $now)
            ->get()
            ->toArray();

        $this->redis->set(self::CACHE_KEY, json_encode($result));
    }

    // 新增或更新訂單
    // public function storeOrder(array $data): void
    // {
    //     $model = Order::findOrNew($data['id']);
    //     $model->user_id = $data['user_id'];
    //     $model->name = $data['name'];
    //     if (! empty($data['image_url'])) {
    //         $model->image_url = $data['image_url'];
    //     }
    //     $model->url = $data['url'];
    //     $model->position = $data['position'];
    //     $model->start_time = $data['start_time'];
    //     $model->end_time = $data['end_time'];
    //     $model->buyer = $data['buyer'];
    //     $model->expire = $data['expire'];
    //     $model->save();
    //     $this->updateCache();
    // }
}
