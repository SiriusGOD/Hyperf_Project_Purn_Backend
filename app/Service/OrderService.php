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

use App\Model\Member;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Product;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class OrderService
{
    public const CACHE_KEY = 'order';

    public const TTL_30_Min = 1800;

    protected Redis $redis;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('Order');
    }

    // 取得訂單
    public function searchOrders($order_number, $order_status, $page = 1): object
    {
        // 顯示幾筆
        $step = Order::PAGE_PER;
        $query = Order::join('users', 'orders.user_id', 'users.id')
            ->select('orders.*', 'users.name');
        if (! empty($order_number)) {
            $query = $query->where('orders.order_number', '=', $order_number);
        } elseif (! empty($order_status)) {
            $query = $query->where('orders.status', '=', $order_status);
        }
        $query = $query->offset(($page - 1) * $step)
            ->orderByDesc('orders.id')
            ->limit($step);
        return $query->get();
    }

    // 取得訂單數
    public function getOrdersCount($order_number, $order_status): int
    {
        $query = Order::select('*');
        if (! empty($order_number)) {
            $query = $query->where('orders.order_number', '=', $order_number);
        } elseif (! empty($order_status)) {
            $query = $query->where('orders.status', '=', $order_status);
        }
        return $query->count();
    }

    // 取得訂單 By User Id
    public function searchUserOrder($user_id, $order_status, $offset = 0, $limit = 0)
    {
        $checkRedisKey = self::CACHE_KEY . ':' . $user_id . ':' . $order_status . ':' . $offset . ':' . $limit;

        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        $query = Order::join('users', 'orders.user_id', 'users.id')
            ->select('orders.*', 'users.name')
            ->where('users.id', '=', $user_id);
        if (! empty($order_status)) {
            $query = $query->where('orders.status', '=', $order_status);
        }
        if (! empty($offset)) {
            $query = $query->offset($offset);
        }
        if (! empty($limit)) {
            $query = $query->limit($limit);
        }
        $orders = $query->get()->toArray();
        foreach ($orders as $key => $value) {
            $orders[$key]['total_price'] = (float) $value['total_price'];
        }

        $this->redis->set($checkRedisKey, json_encode($orders));
        $this->redis->expire($checkRedisKey, self::TTL_30_Min);

        return $orders;
    }

    // 建立訂單
    public function createOrder($arr)
    {
        // 撈取會員資料
        // $user = Member::find($user_id)->toArray();
        $user = $arr['user'];
        // 撈取商品資料
        // $product = Product::find($prod_id)->toArray();
        $product = $arr['product'];

        $data['order'] = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'telephone' => $user['phone'],
            'payment_type' => $arr['payment_type'],
            // 'currency' => $product['currency'],
            // 'total_price' => $product['selling_price'] ?? 0,
            'pay_way' => Order::PAY_WAY_MAP_NEW[$arr['payment_type']],
            'pay_url' => $arr['pay_url'] ?? '',
            'pay_proxy' => $arr['pay_proxy'],
            'pay_order_id' => $arr['pay_order_id'] ?? '',
        ];
        switch ($arr['pay_method']) {
            case 'cash':
            case 'coin':
                $data['order']['currency'] = $product['currency'];
                $data['order']['total_price'] = $product['selling_price'] ?? 0;
                break;

            case 'diamond_coin':
                $data['order']['currency'] = Order::PAY_CURRENCY['diamond_coin'];
                $data['order']['total_price'] = $product['diamond_price'] ?? Product::DIAMOND_PRICE;
                break;
            
            default:
                $data['order']['currency'] = Order::PAY_CURRENCY[$arr['pay_method']];
                $data['order']['total_price'] = Product::QUOTA;
                break;
        }
        $data['product'] = [
            'product_id' => $arr['prod_id'],
            'product_name' => $product['name'],
            'product_currency' => $product['currency'],
            'product_selling_price' => $product['selling_price'],
        ];

        // 新增訂單
        return $this->storeOrder($data);
    }

    // 新增訂單
    public function storeOrder(array $data)
    {
        Db::beginTransaction();
        try {
            $order_number = self::getSn();
            // insert orders table
            $model = new Order();
            $model->user_id = $data['order']['user_id'];
            $model->order_number = $order_number;
            $model->pay_order_id = $data['order']['pay_order_id'];
            $model->pay_third_id = '';
            $model->address = '';
            $model->email = isset($data['order']['email']) ? $data['order']['email'] : '';
            $model->mobile = '';
            $model->telephone = isset($data['order']['telephone']) ? $data['order']['telephone'] : '';
            $model->payment_type = $data['order']['payment_type'];
            $model->currency = $data['order']['currency'];
            $model->total_price = $data['order']['total_price'];
            $model->pay_way = $data['order']['pay_way'];
            $model->pay_url = $data['order']['pay_url'];
            $model->pay_proxy = $data['order']['pay_proxy'];
            $model->save();

            // get order id
            $res = Order::select('id')->where('order_number', $order_number)->get()->toArray();
            $id = $res[0]['id'];

            // insert orders_details table
            $model = new OrderDetail();
            $model->order_id = $id;
            $model->product_id = $data['product']['product_id'];
            $model->product_name = $data['product']['product_name'];
            $model->product_currency = $data['product']['product_currency'];
            $model->product_selling_price = $data['product']['product_selling_price'];
            $model->save();
            Db::commit();
            $this->updateCache($data['order']['user_id']);
            return $order_number;
        } catch (\Throwable $ex) {
            $this->logger->error($ex->getMessage(), $data);
            Db::rollBack();
            return false;
        }
    }

    // 刪除訂單
    public function delete($user_id, $order_num, $order_status)
    {
        $query = Order::where([
            ['user_id', '=', $user_id],
            ['order_number', '=', $order_num],
        ]);
        $record = $query->first();

        // 查無訂單或該訂單已是取消／刪除狀態
        if (empty($record) || $record->status == Order::ORDER_STATUS['delete']) {
            return false;
        }

        $record->status = $order_status;
        $record->save();
        return true;
    }

    // 更新快取
    public function updateCache($user_id): void
    {
        $checkRedisKey = self::CACHE_KEY . ':' . $user_id . '::0:0';
        $query = Order::join('users', 'orders.user_id', 'users.id')
            ->select('orders.*', 'users.name')
            ->where('users.id', '=', $user_id);
        $result = $query->get()->toArray();

        $this->redis->set($checkRedisKey, json_encode($result));
        $this->redis->expire($checkRedisKey, self::TTL_30_Min);
    }

    // 產生當天訂單流水號
    public function getSn()
    {
        $sql = 'SELECT o.order_number '
            . 'FROM orders AS o '
            . 'ORDER BY o.order_number DESC LIMIT 1';
        $res = Db::select($sql);

        if (! isset($res[0]->order_number)) {
            $lastNum = 0;
        } else {
            $lastNum = (int) mb_substr($res[0]->order_number, -5, 5);
        }

        $orderSn = 'PO' . date('Ymd', $_SERVER['REQUEST_TIME']) . str_pad((string) ($lastNum + 1), 5, '0', STR_PAD_LEFT);

        return $orderSn;
    }
}
