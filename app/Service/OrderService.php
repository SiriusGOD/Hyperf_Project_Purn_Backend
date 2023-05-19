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

use App\Model\BuyMemberLevel;
use App\Model\Member;
use App\Model\MemberLevel;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\Pay;
use App\Model\Product;
use Carbon\Carbon;
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
    public function searchOrders($order_number, $order_status, $page = 0): object
    {
        // 顯示幾筆
        $step = Order::PAGE_PER;
        $query = Order::join('members', 'orders.user_id', 'members.id')
            ->select('orders.*', 'members.name');
        if (! empty($order_number)) {
            $query = $query->where('orders.order_number', '=', $order_number);
        } elseif (! empty($order_status)) {
            $query = $query->where('orders.status', '=', $order_status);
        }
        $query = $query->offset($page * $step)
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

        $query = Order::join('members', 'orders.user_id', 'members.id')
            ->select('orders.*', 'members.name')
            ->where('members.id', '=', $user_id);
        if (! empty($order_status)) {
            $query = $query->where('orders.status', '=', $order_status);
        }
        if (! empty($offset)) {
            $query = $query->offset($offset * $limit);
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
        // 撈取支付方式
        if ($arr['payment_type'] == 0) {
            $pay_way['pronoun'] = Order::PAY_WAY_MAP_NEW[$arr['payment_type']];
        } else {
            $pay_way = Pay::select('pronoun')->where('id', $arr['payment_type'])->first()->toArray();
        }
        $data['order'] = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'telephone' => $user['phone'],
            'payment_type' => $arr['payment_type'],
            // 'currency' => $product['currency'],
            // 'total_price' => $product['selling_price'] ?? 0,
            // 'pay_way' => Order::PAY_WAY_MAP_NEW[$arr['payment_type']],
            'pay_way' => $pay_way['pronoun'],
            'pay_url' => $arr['pay_url'] ?? '',
            'pay_proxy' => $arr['pay_proxy'],
            'pay_order_id' => $arr['pay_order_id'] ?? '',
        ];
        switch ($arr['pay_method']) {
            case 'cash':
                $data['order']['currency'] = $product['currency'];
                $data['order']['total_price'] = $product['selling_price'] ?? 0;
                break;
            case 'coin':
                $data['order']['currency'] = Order::PAY_CURRENCY['coin'];
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

            $this -> delMemberListCache($data['order']['user_id']);

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

        $this -> delMemberListCache($record-> user_id);


        return true;
    }

    // 更新快取
    public function updateCache($user_id): void
    {
        $checkRedisKey = self::CACHE_KEY . ':' . $user_id . '::0:0';
        $query = Order::join('members', 'orders.user_id', 'members.id')
            ->select('orders.*', 'members.name')
            ->where('members.id', '=', $user_id);
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

    // 會員升等
    public function memberLevelUp($data)
    {
        $product = Product::where('id', $data['prod_id'])->first();
        $member = Member::where('id', $data['user_id'])->where('status', '<', Member::STATUS['DISABLE'])->first();
        // 獲取會員卡天數
        $member_level = MemberLevel::where('id', $product->correspond_id)->first();
        $duration = $member_level->duration;
        $level = $member_level->type;
        // 確認該會員是否已有會員資格
        // 沒有會員資格
        if ($member->member_level_status != MemberLevel::TYPE_VALUE['vip'] && $member->member_level_status != MemberLevel::TYPE_VALUE['diamond']) {
            // 新增會員的會員等級持續時間
            $now = Carbon::now();
            $buy_member_level = new BuyMemberLevel();
            $buy_member_level->member_id = $member->id;
            $buy_member_level->member_level_type = $level;
            $buy_member_level->member_level_id = $member_level->id;
            $buy_member_level->order_number = $data['order_number'];
            $buy_member_level->start_time = $now->toDateTimeString();
            $buy_member_level->end_time = $now->addDays($duration)->toDateTimeString();
            $buy_member_level->save();

            // 更新會員的會員等級
            // 當是購買鑽石或vip會員1天卡 則會限制當天觀看數為50部
            if ($duration == 1) {
                switch ($level) {
                    case 'vip':
                        $member->vip_quota = MemberLevel::LIMIT_QUOTA;
                        break;
                    case 'diamond':
                        $member->diamond_quota = MemberLevel::LIMIT_QUOTA;
                        break;
                }
            } else {
                switch ($level) {
                    case 'vip':
                        $member->vip_quota = null;
                        break;
                    case 'diamond':
                        $member->diamond_quota = null;
                        break;
                }
            }
            $member->member_level_status = MemberLevel::TYPE_VALUE[$level];
            $member->save();

            var_dump('新增');
        } else {
            // 有會員資格
            $buy_member_level = BuyMemberLevel::where('member_id', $member->id)
                ->where('member_level_type', $level)
                ->whereNull('deleted_at')
                ->first();
            if (empty($buy_member_level)) {
                // 新增會員的會員等級持續時間
                $now = Carbon::now();
                $buy_member_level = new BuyMemberLevel();
                $buy_member_level->member_id = $member->id;
                $buy_member_level->member_level_type = $level;
                $buy_member_level->member_level_id = $member_level->id;
                $buy_member_level->order_number = $data['order_number'];
                $buy_member_level->start_time = $now->toDateTimeString();
                $buy_member_level->end_time = $now->addDays($duration)->toDateTimeString();
                $buy_member_level->save();

                if ($level == 'diamond' && $member->member_level_status != MemberLevel::TYPE_VALUE[$level]) {
                    // 更新會員的會員等級
                    // 當是購買鑽石或vip會員1天卡 則會限制當天觀看數為50部
                    if ($duration == 1) {
                        $member->diamond_quota = MemberLevel::LIMIT_QUOTA;
                    } else {
                        $member->diamond_quota = null;
                    }
                    $member->member_level_status = MemberLevel::TYPE_VALUE[$level];
                    $member->save();
                }

                if ($duration == 1 && $level == 'vip') {
                    $member->vip_quota = MemberLevel::LIMIT_QUOTA;
                    $member->save();
                } elseif ($duration != 1 && $level == 'vip') {
                    $member->vip_quota = null;
                    $member->save();
                }
                var_dump('更新 -> 新增一筆');
            } else {
                $end_time = $buy_member_level->end_time;
                $buy_member_level->end_time = Carbon::parse($end_time)->addDays($duration)->toDateTimeString();
                $buy_member_level->save();

                // 不是一天的會員卡 次數要改成null
                if ($duration > 1) {
                    switch ($level) {
                        case 'vip':
                            $member->vip_quota = null;
                            break;
                        case 'diamond':
                            $member->diamond_quota = null;
                            break;
                    }
                    $member->save();
                }else if ($duration == 1){
                    switch ($level) {
                        case 'vip':
                            $member->vip_quota = $member->vip_quota + MemberLevel::LIMIT_QUOTA;
                            break;
                        case 'diamond':
                            $member->diamond_quota = $member->diamond_quota + MemberLevel::LIMIT_QUOTA;
                            break;
                    }
                    $member->save();
                }
                var_dump('更新 -> 延長');
            }
        }
    }

    // 會員降等
    public function memberLevelDown($user_id)
    {
        $now = Carbon::now()->toDateTimeString();
        // 查詢會員等級
        $member = Member::select('member_level_status')->where('id', $user_id)->first()->toArray();
        $member_level = $member['member_level_status'];

        switch ($member_level) {
            case MemberLevel::TYPE_VALUE['diamond']:
                // 確認是否有vip會員資格
                $vip = BuyMemberLevel::where('member_id', $user_id)
                    ->where('member_level_type', MemberLevel::TYPE_LIST[0])
                    ->whereNull('deleted_at')
                    ->first();
                if (! empty($vip)) {
                    if ($vip->end_time <= $now) {
                        // vip 也超過時間
                        $vip->delete();

                        $status = MemberLevel::NO_MEMBER_LEVEL;
                        $vip_quota_flag = true;
                        $diamond_quota_flag = false;
                    } else {
                        // vip 沒超過時間
                        $status = MemberLevel::TYPE_VALUE['vip'];
                        $vip_quota_flag = false;
                        $diamond_quota_flag = true;
                    }
                } else {
                    $status = MemberLevel::NO_MEMBER_LEVEL;
                    $vip_quota_flag = true;
                    $diamond_quota_flag = true;
                }

                // 變更會員狀態
                $up_member = Member::where('id', $user_id)->first();
                $up_member->member_level_status = $status;
                if ($diamond_quota_flag) {
                    // 鑽石次數歸0
                    $up_member->diamond_quota = 0;
                }
                if ($vip_quota_flag) {
                    // vip次數歸0
                    $up_member->vip_quota = 0;
                }
                $up_member->save();

                // 移除鑽石會員等級的持續時間
                $diamond = BuyMemberLevel::where('member_id', $user_id)
                    ->where('member_level_type', MemberLevel::TYPE_LIST[1])
                    ->whereNull('deleted_at')
                    ->first();
                if(!empty($diamond))$diamond->delete();
                break;
            case MemberLevel::TYPE_VALUE['vip']:
                // 變更會員狀態
                $up_member = Member::where('id', $user_id)->first();
                $up_member->member_level_status = MemberLevel::NO_MEMBER_LEVEL;
                $up_member->vip_quota = 0;
                $up_member->save();

                // 移除VIP會員等級的持續時間
                $vip = BuyMemberLevel::where('member_id', $user_id)
                    ->where('member_level_type', MemberLevel::TYPE_LIST[0])
                    ->whereNull('deleted_at')
                    ->first();
                if(!empty($vip))$vip->delete();
                break;
        }
    }

    // 刪除會員購買紀錄的快取
    public function delMemberListCache($user_id)
    {
        $service = make(MemberService::class);
        $service->delMemberListRedis($user_id);
    }

    // 刪除會員快取
    public function delMemberRedis($user_id)
    {
        $service = make(MemberService::class);
        $service->delMemberRedis($user_id);
    }
}
