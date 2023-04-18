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

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('Pay');
    }

    // 產生支付鏈接
    public function getPayUrl($user_id, $prod_id, $payment_type, $oauth_type, $pay_proxy, $ip): bool|array|string
    {
        // 測試
        if (env('APP_ENV') != 'production') {
            $result['success'] = true;
            $result['data']['pay_url'] = 'http://test.pay/' . $this->randomURL();
            $result['data']['pay_way'] = 'test';
            $result['data']['pay_proxy'] = 'online';
            $result['data']['pay_order_id'] = 'test_order_id_' . $this->randomURL();
        } else {
            // 正式 (test)
            // 撈取商品資料
            $product = Product::find($prod_id)->toArray();

            $member_aff = 'testcode'; // 之後改成從redis獲取 (邀请码)
            $data['app_name'] = env('APP_NAME');
            $data['app_type'] = ($oauth_type == 'web') ? 'pc' : $oauth_type;
            $data['aff'] = "{$member_aff}:{$prod_id}"; // 区分 '邀請碼 :产品'
            $data['amount'] = (string) $product['selling_price'];
            $sign = $this->make_sign_pay($data, env('PAY_SIGNKEY'));
            $data['ip'] = $ip;
            $data['pay_type'] = Order::PAY_WAY_MAP_NEW[$payment_type];
            $data['type'] = isset($pay_proxy) ? $pay_proxy : 'online';
            $data['sign'] = $sign;
            $data['is_sdk'] = 0; // 未知欄位
            $data['product'] = 'vip'; // vip or coins

            $result = $this->curlPost(env('PAY_URL'), $data);

            if (! isset($result['success']) && $result['success'] != true) {
                $this->logger->error('生成支付鏈接失敗', $result);
            }
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
        return $result;
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
        $this->logger->info('呼叫回調函式', $data);
        $signdata = $data;
        if (isset($data['success']) && $data['success'] == 200) { // 付款成功
            // 簽名驗證
            unset($signdata['sign']);
            $sign = $this->make_sign_callback($signdata, env('PAY_SIGNKEY'));
            if ($sign != $data['sign']) {
                $this->logger->error('簽名驗證失敗', $data);
                return '簽名驗證失敗';
            }

            // 查詢對應訂單
            $order = Order::join('order_details', 'orders.id', 'order_details.order_id')
                ->select('orders.id', 'orders.user_id', 'orders.order_number', 'order_details.product_id', 'orders.status', 'orders.total_price')
                ->where('pay_order_id', $data['order_id'])->first();
            if (empty($order)) {
                $this->logger->error('查無對應系統訂單', $data);
                return '查無對應系統訂單';
            }

            // 比對訂單狀態 須為訂單成立狀態
            if ($order->status != Order::ORDER_STATUS['create']) {
                $data['sys_order_id'] = $order->id;
                $this->logger->error('該訂單狀態並非訂單成立', $data);
                return '該訂單狀態並非訂單成立';
            }

            // 確認訂單對應商品是否存在
            $product = Product::where('id', $order->product_id)->first();
            if (empty($product)) {
                $this->logger->error('查無對應系統訂單的商品', $data);
                return '查無對應系統訂單的商品';
            }

            // 確認訂單對應商品是否是上架狀態
            if ($product->expire != Product::EXPIRE['no']) {
                $this->logger->error('此商品已下架', $data);
                return '此商品已下架';
            }

            // 確認訂單用戶是否存在
            $member = Member::where('id', $order->user_id)->where('status', '<', Member::STATUS['DISABLE'])->first();
            if (empty($member)) {
                $this->logger->error('查無訂單用戶', $data);
                return '查無訂單用戶';
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
                    if ($product->type == Product::TYPE_LIST[2]) {
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
                                $buy_member_level->order_number = $order->order_number;
                                $buy_member_level->start_time = $now->toDateTimeString();
                                $buy_member_level->end_time = $now->addDays($duration)->toDateTimeString();
                                $buy_member_level->save();

                                if ($level == 'diamond' && $member->member_level_status != MemberLevel::TYPE_VALUE[$level]) {
                                    // 更新會員的會員等級
                                    $member->member_level_status = MemberLevel::TYPE_VALUE[$level];
                                    $member->save();
                                }
                                var_dump('更新 -> 新增一筆');
                            } else {
                                $end_time = $buy_member_level->end_time;
                                $buy_member_level->end_time = Carbon::parse($end_time)->addDays($duration)->toDateTimeString();
                                $buy_member_level->save();
                                var_dump('更新 -> 延長');
                            }
                        }
                    }
                    // 儲值點數 現金點數 鑽石點數
                }

                Db::commit();
            } catch (\Throwable $th) {
                // throw $th;
                Db::rollBack();
                $this->logger->error('error:' . __LINE__ . json_encode($th->getMessage()));
                var_dump($th->getMessage());
            }
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
}
