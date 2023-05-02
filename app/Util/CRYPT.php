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
namespace App\Util;

class CRYPT
{
    public $ENCRYPTION_KEY;
    public $app_id;
    public $encryptKey;
    public $appKey;
    public $channel_id;
    private static $debug;

    // 定义加密密钥
    //public function __construct()
    //{
    //    self::$ENCRYPTION_KEY = env('ENCRYPT_KEY');
    //}

    // 加密函数
    //public static function encrypt($plaintext)
    //{
    //    $ENCRYPTION_KEY = env('ENCRYPT_KEY');
    //    $cipher = 'AES-256-CBC';
    //    // 加密算法
    //    $ivlen = openssl_cipher_iv_length($cipher);
    //    // 获取初始化向量长度
    //    $iv = openssl_random_pseudo_bytes($ivlen);
    //    // 生成随机初始化向量
    //    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);
    //    $hmac = hash_hmac('sha256', $ciphertext_raw, $ENCRYPTION_KEY, true);
    //    // 计算 HMAC
    //    return base64_encode($iv . $hmac . $ciphertext_raw);
    //    // 对 HMAC 和密文进行编码
    //}

    //// 解密
    //public static function decrypt($ciphertext)
    //{
    //    $ENCRYPTION_KEY = env('ENCRYPT_KEY');
    //    $cipher = 'AES-256-CBC';
    //    // 加密算法
    //    $c = base64_decode($ciphertext);
    //    // 解码密文
    //    $ivlen = openssl_cipher_iv_length($cipher);
    //    // 获取初始化向量长度
    //    $iv = substr($c, 0, $ivlen);
    //    // 从密文中提取初始化向量
    //    $hmac = substr($c, $ivlen, $sha2len = 32);
    //    // 从密文中提取 HMAC
    //    $ciphertext_raw = substr($c, $ivlen + $sha2len);
    //    // 从密文中提取加密后的原始数据
    //    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);
    //    // 使用加密密钥和初始化向量解密密文
    //    $calcmac = hash_hmac('sha256', $ciphertext_raw, $ENCRYPTION_KEY, true);
    //    // 计算 HMAC
    //    if (hash_equals($hmac, $calcmac)) {
    //        // 比较 HMAC 是否一致
    //        return $original_plaintext;
    //    }
    //    return null;
    //}

    public function __construct($appId = '',$debug =false)
    {
        self::$debug = $debug;
        // $appId && $this->initAppId($appId);
        $this->initAppId($appId);
    }

    public function test()
    {
        echo 'test ffff encripy';
    }

    #签名
    public static function make_sign($array, $signKey)
    {
        if (empty($array)) return '';
        ksort($array);
        //$string = http_build_query($array);

        $arr_temp = array();
        foreach ($array as $key => $val) {
            $arr_temp[] = $key . '=' . $val;
        }
        $string = implode('&', $arr_temp);

        $string = $string . $signKey;

        #先sha256签名 再md5签名
        $sign_str = md5(hash('sha256', $string));
        return $sign_str;
    }

    #验证
    public static function check_sign($array, $signKey)
    {
        if (!isset($array['sign']) || $array['sign'] == '') {
            return false;
        }
        $sign = $array['sign'];

        unset($array['sign']);
        $msg = "我方计算签名，{$signKey} # " . self::make_sign($array, $signKey);

        //   echo "对方签名，$sign";
        // echo  "我方计算签名，".self::make_sign($array,$signKey);


        self::$debug && trigger_error($msg, E_USER_NOTICE);
        self::$debug && trigger_error("对方签名，$sign", E_USER_NOTICE);

        return self::make_sign($array, $signKey) === $sign;
    }
    #@todo AES加解密
    #加密
    public static function encrypt($input, $cryptKey)
    {
        //$ENCRYPTION_KEY = env('ENCRYPT_KEY');
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cfb'));
        $key_iv = self::EVPBytesToKey($cryptKey);
        $data = openssl_encrypt($input, 'aes-256-cfb', $key_iv[0], OPENSSL_RAW_DATA, $iv);
        $data = $iv . $data;
        $data = strtoupper(bin2hex($data));
        return $data;
    }

    //解密
    public static function decrypt($input, $cryptKey)
    {
        $input = hex2bin($input);
        $iv_len = openssl_cipher_iv_length('aes-256-cfb');
        $iv = substr($input, 0, $iv_len);
        $input = substr($input, $iv_len);
        $key_iv = self::EVPBytesToKey($cryptKey);
        $decrypted = openssl_decrypt($input, 'aes-256-cfb', $key_iv[0], OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    #@todo AES加解密
    #加密
    public static function encryptRaw($input, $cryptKey)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cfb'));
        $key_iv = self::EVPBytesToKey($cryptKey);
        $data = openssl_encrypt($input, 'aes-256-cfb', $key_iv[0], OPENSSL_RAW_DATA, $iv);
        $data = $iv . $data;
        return $data;
    }

    //解密
    public static function decryptRaw($input, $cryptKey)
    {
        $iv_len = openssl_cipher_iv_length('aes-256-cfb');
        $iv = substr($input, 0, $iv_len);
        $input = substr($input, $iv_len);
        $key_iv = self::EVPBytesToKey($cryptKey);
        $decrypted = openssl_decrypt($input, 'aes-256-cfb', $key_iv[0], OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }

    public static function encryptBase64($data, $salt)
    {
        return base64_encode(LibCrypt::encryptRaw(json_encode($data), $salt));
    }

    public static function decryptBase64($data, $salt)
    {
        return @json_decode(LibCrypt::decryptRaw(base64_decode($data), $salt), true);
    }



    public static function EVPBytesToKey($password, $key_len = '32', $iv_len = '16')
    {
        $cache_key = "$password:$key_len:$iv_len";

        $m = array();
        $i = 0;
        $count = 0;
        while ($count < $key_len + $iv_len) {
            $data = $password;
            if ($i > 0) {
                $data = $m[$i - 1] . $password;
            }
            $d = md5($data, true);
            $m[] = $d;
            $count += strlen($d);
            $i += 1;
        }
        $ms = '';
        foreach ($m as $buf) {
            $ms .= $buf;
        }
        $key = substr($ms, 0, $key_len);
        $iv = substr($ms, $key_len, $key_len + $iv_len);

        return array($key, $iv);
    }

    #使用post的传输
    public static function CURL_POST($url, $data)
    {
        //启动一个CURL会话
        $ch = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //忽略证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        if (!is_null(json_decode($data, false))) {//如果是json数据，添加头信息
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        } else {
            //curl_setopt( $ch, CURLOPT_HTTPHEADER,['Content-Type:multipart/form-data']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:x-www-form-urlencoded']);
        }
        //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);//可post多维数组
        //curl_setopt($ch, CURLOPT_HEADER,0);//是否需要头部信息（否）
        // 执行操作
        $result = curl_exec($ch);


        $msg = "我方向 $url 异步回调发送的数据\n " . print_r($data, true);
        self::$debug && trigger_error($msg, E_USER_NOTICE);
        curl_close($ch);

        #返回数据
        return $result;
    }

    /*
     *
     */
    public function replyData($data = '', $errcode = 0)
    {
        $return['errcode'] = (int)$errcode;
        $return['timestamp'] = time();
        $return['data'] = $data;
        $msg = "我方，返回数据：" . print_r($return, true);
        if (!empty($return['data'])) {
            // $return['data'] = self::encrypt(json_encode($return['data'],JSON_UNESCAPED_UNICODE),$this->encryptKey);
            $return['data'] = self::encrypt(json_encode($return['data'], JSON_UNESCAPED_UNICODE), $this->encryptKey);
            $return['sign'] = self::make_sign($return, $this->appKey);
            // return json_encode($return,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            return json_encode($return, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return '';
    }

    /*
     * 验证第三方过来的数据是否合法，以及解密
     */
    public function checkInputData($data, $resetKey = true)
    {
        if ($resetKey) {
            $appId = isset($data['appId']) ? $data['appId'] : '0';
            $this->initAppId($appId);
        }
        //var_dump([$this->appKey,$this->encryptKey]);die;
        self::$debug && trigger_error("我方收到未验证的数据：" . print_r($data, true), E_USER_NOTICE);

        if (!self::check_sign($data, $this->appKey)) {
            self::$debug && trigger_error("我方收到，数据--签名验证失败", E_USER_NOTICE);
            return false;
        }
        $json = self::decrypt($data['data'], $this->encryptKey);
        self::$debug && trigger_error("我方收到，解密后的 json 数据--\n" . print_r($json, true), E_USER_NOTICE);
        $tmpData = json_decode($json, true);

        self::$debug && trigger_error("我方收到，解密后的 json_decode 数据--\n" . print_r($tmpData, true), E_USER_NOTICE);
        return $tmpData;
    }

    public function initAppId($appId = '')
    {
        $autoSign['app_id'] = "68ccdfadf6ffddcgbsd2dgc9077f8c62";//appId
        $autoSign['app_key'] = "kihfks3kjdhfksjh3kdjfs745dkslfh4";//数据签名key
        $autoSign['encrypt_key'] = "ljhlksslgkjfhlksuo8472rju6p2od03";//数据加密key
        $autoSign['channel_id'] = "123";//渠道ID

        if (!$autoSign) return false;
        $this->appKey = $autoSign['app_key'];//数据签名key
        $this->encryptKey = $autoSign['encrypt_key'];//数据加密key
        $this->channel_id = (int)$autoSign['channel_id'];//渠道ID
        $this->app_id = $appId;
    }


    public function setKey($signKey , $encryptKey){
        $this->appKey = $signKey;
        $this->encryptKey = $encryptKey;
        return $this;
    }
}
