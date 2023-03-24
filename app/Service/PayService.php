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
use App\Model\Product;
use Hyperf\Redis\Redis;

class PayService
{
    public const CACHE_KEY = 'Pay';

    public const TTL_30_Min = 1800;

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 產生支付鏈接
    public function getPayUrl($user_id, $prod_id, $payment_type, $oauth_type, $pay_proxy, $ip)
    {
        // 測試
        if (env('APP_ENV') != 'production') {
            $result['success'] = true;
            $result['data']['pay_url'] = 'http://test.pay/' . $this->randomURL();
            $result['data']['pay_way'] = 'test';
            $result['data']['pay_proxy'] = 'online';
        } else {
            // 正式 (test)
            // 撈取商品資料
            $product = Product::find($prod_id)->toArray();

            $member_aff = 'testcode'; // 之後改成從redis獲取
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
}
