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
use App\Model\Coin;
use App\Model\Member;
use App\Model\MemberLevel;
use App\Model\Order;
use App\Model\Pay;
use App\Model\Product;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class PayService
{
    public const CACHE_KEY = 'Pay';

    public const TTL_30_Min = 1800;

    protected Redis $redis;

    protected $proxyService;
    protected $channelService;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory, ChannelService $channelService, ProxyService $proxyService)
    {
        $this->redis = $redis;
        $this->channelService = $channelService;
        $this->logger = $loggerFactory->get('Pay');
        $this->proxyService = $proxyService;
    }

    // 產生支付鏈接
    public function getPayUrl($arr): bool|array|string
    {
        // 測試
        if (env('APP_ENV') != 'prod' && env('APP_ENV') != 'production') {
            $result['success'] = true;
            $result['data']['pay_url'] = 'http://test.pay/' . $this->randomURL();
            $result['data']['pay_way'] = 'test';
            $result['data']['pay_proxy'] = 'online';
            $result['data']['pay_order_id'] = 'test_order_id_' . $this->randomURL();
        } else {
            // 正式 (test)
            // 撈取商品資料
            // $product = Product::find($arr['prod_id'])->toArray();
            $product = $arr['product'];

            $member_aff = $arr['user']['aff']; // 之後改成從redis獲取 (邀请码)
            $data['app_name'] = env('APP_NAME');
            $data['app_type'] = ($arr['oauth_type'] == 'web') ? 'pc' : $arr['oauth_type'];
            $data['aff'] = "{$member_aff}:{$arr['prod_id']}"; // 区分 '邀請碼 :产品'
            $data['amount'] = (string) $product['selling_price'];
            $sign = $this->make_sign_pay($data, env('PAY_SIGNKEY'));
            $data['ip'] = $arr['ip'];
            
            // 撈取支付方式
            if ($arr['payment_type'] == 0) {
                $pay_way['pronoun'] = Order::PAY_WAY_MAP_NEW[$arr['payment_type']];
            } else {
                $pay_way = Pay::select('pronoun')->where('id', $arr['payment_type'])->first()->toArray();
            }
            $data['pay_type'] = $pay_way['pronoun'];
            $data['type'] = isset($arr['pay_proxy']) ? $arr['pay_proxy'] : 'online';
            $data['sign'] = $sign;
            $data['is_sdk'] = 0; // 未知欄位
            $data['product'] = $product['type'] == Product::TYPE_CORRESPOND_LIST['member'] ? 'vip' : 'coins'; // vip or coins

            $this->logger->info("payurl ". __LINE__ .json_encode($data));
            $this->logger->info("payurl ". __LINE__ .env('PAY_URL'));
            $result = $this->curlPost(env('PAY_URL'), $data);
            if (! isset($result['success']) && $result['success'] != true) {
                $this->logger->error('生成支付鏈接失敗', $result);
            }
            $result['data']['pay_way'] = $pay_way['pronoun'];
        }
        return $result;
    }

    // curl
    public function curlPost($url, $params = [], $timeout = 30)
    { // 模拟提交数据函数
        $post = htmlspecialchars_decode(! empty($params) ? http_build_query($params) : '');
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // 可post多维数组

        $result = curl_exec($ch);
        // print_r($result);die;
        /* if($result === false) {
             echo 'Curl error: ' . curl_error($ch);
         }*/
        curl_close($ch);
        return json_decode($result, true);
    }

    // 亂處產生一個測試網址
    public function randomURL($URLlength = 8)
    {
        $url = '';
        $charray = array_merge(range('a', 'z'), range('0', '9'));
        $max = count($charray) - 1;
        for ($i = 0; $i < $URLlength; ++$i) {
            $randomChar = mt_rand(0, $max);
            $url .= $charray[$randomChar];
        }
        return $url;
    }

    /**
     * 生成支付签名.
     * @param string $signKey
     * @param mixed $array
     * @return string
     */
    public function make_sign_pay($array, $signKey = '')
    {
        if (empty($array)) {
            return '';
        }
        $string = '';
        foreach ($array as $key => $val) {
            $string .= $val;
        }

        return md5($string . $signKey);
    }

    // 支付 回調函式
    public function notifyPayAction($data)
    {
        errLog(var_export($data,true),"notifyPayAction");
        $this->logger->info('呼叫回調函式', $data);
        $signdata = $data;
        if (isset($data['success']) && $data['success'] == 200) { // 付款成功
            // 簽名驗證
            unset($signdata['sign']);
            $sign = $this->make_sign_callback($signdata, env('PAY_SIGNKEY'));
            if ($sign != $data['sign']) {
                // $this->logger->error('簽名驗證失敗', $data);
                // return '簽名驗證失敗';
            }

            // 查詢對應訂單
            $order = Order::join('order_details', 'orders.id', 'order_details.order_id')
                ->select('orders.id', 'orders.user_id', 'orders.order_number', 'order_details.product_id', 'orders.status', 'orders.total_price')
                ->where('pay_order_id', $data['order_id'])->first();
            if (empty($order)) {
                $this->logger->error('查無對應系統訂單', $data);
                return trans('api.pay_control.search_no_order');
            }

            // 比對訂單狀態 須為訂單成立狀態
            if ($order->status != Order::ORDER_STATUS['create']) {
                $data['sys_order_id'] = $order->id;
                $this->logger->error('該訂單狀態並非訂單成立', $data);
                return trans('api.pay_control.no_create_order_status');
            }

            // 確認訂單對應商品是否存在
            $product = Product::where('id', $order->product_id)->first();
            if (empty($product)) {
                $this->logger->error('查無對應系統訂單的商品', $data);
                return trans('api.pay_control.no_order_product');
            }

            // 確認訂單對應商品是否是上架狀態
            if ($product->expire != Product::EXPIRE['no']) {
                $this->logger->error('此商品已下架', $data);
                return trans('api.pay_control.product_off_shelf');
            }
            // 確認訂單用戶是否存在
            $member = Member::where('id', $order->user_id)->where('status', '<', Member::STATUS['DISABLE'])->first();
            if (empty($member)) {
                $this->logger->error('查無訂單用戶', $data);
                return trans('api.pay_control.search_no_member');
            }

            // 更新訂單狀態
            try {
                $order_amount = $order->total_price;
                $real_pay_amount = $data['pay_money'];
                Db::beginTransaction();
                // 誤差在人民幣４元內都是正常
                if ($order_amount > 0 && ($real_pay_amount >= ($order_amount - 4))) {
                    $order->pay_third_id = $data['third_id'];
                    $order->pay_amount = (float) $real_pay_amount;
                    $order->updated_at = date('Y-m-d H:i:s', (int) $data['pay_time']);
                    $order->status = Order::ORDER_STATUS['finish'];
                    $order->save();

                    // 會員卡
                    if ($product->type == Product::TYPE_CORRESPOND_LIST['member']) {
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
                            $buy_member_level->order_number = $order->order_number;
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

                            errLog('新增');
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
                                $buy_member_level->order_number = $order->order_number;
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
                                }
                                var_dump('更新 -> 延長');
                            }
                        }
                    } else {
                        // 儲值點數 現金點數
                        $coin = Coin::where('id', $product->correspond_id)->first();
                        if ($coin->type == Coin::TYPE_LIST[0]) {
                            $member->coins = (float) $member->coins + $coin->points;
                        }
                        // 儲值點數 鑽石點數
                        if ($coin->type == Coin::TYPE_LIST[1]) {
                            $member->diamond_coins = (float) $member->diamond_coins + $coin->points + $coin->bonus;
                        }
                        $member->save();
                        $this->channelService->setChannelRedis($member->aff_url , "archievement" ,$order_amount);
                        var_dump('儲值現金點數成功');
                    }
                }

                Db::commit();
                // 查看代理等級給返傭
                if ($order->status == Order::ORDER_STATUS['finish']) {
                    // 返傭
                    //改寫在order/create
                    //存入我的收益明細
                    //$this->proxyService->rebate($member, $order, $product);
                }

                $this -> delMemberRedis($member->id);
                $this->logger->info('執行結束', $data);
            } catch (\Throwable $th) {
                // throw $th;
                Db::rollBack();
                $this->logger->error('notifyPayAction  error:' . __LINE__ . json_encode($th->getMessage()));
                var_dump($th->getMessage());
            }
        }else{
            // 付款失敗
            $order = Order::where('pay_order_id', $data['order_id'])->first();
            $order->status = Order::ORDER_STATUS['failure'];
            $order->save();
        }
    }

    # 签名 对接第三方支付的签名
    public function make_sign_callback($array, $signKey = '')
    {
        if (empty($array)) {
            return '';
        }

        ksort($array);
        $string = http_build_query($array) . $signKey;
        $string = str_replace('amp;', '', $string);
        return md5($string);
    }

    public function store(array $params): void
    {
        $model = Pay::where('id', $params['id'])->first();
        if (empty($model)) {
            $model = new Pay();
        }
        $model->user_id = $params['user_id'];
        $model->pronoun = $params['pronoun'];
        $model->proxy = $params['proxy'];
        $model->name = $params['name'];
        $model->expire = $params['expire'];
        $model->save();

        $this->delMemberProductKey();
    }

    public function delMemberProductKey()
    {
        $checkRedisKey = "product:";
        $keys = $this->redis->keys( $checkRedisKey.'*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    public function getPayList()
    {
        return Pay::select('id', 'name', 'pronoun')->where('expire', Pay::EXPIRE['no'])->get();
    }

    // 刪除會員快取
    public function delMemberRedis($user_id)
    {
        $service = make(MemberService::class);
        $service->delMemberRedis($user_id);
    }
}
