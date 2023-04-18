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
    // 定义加密密钥
    public function __construct()
    {
      self::$ENCRYPTION_KEY = env('ENCRYPT_KEY');
    }

    // 加密函数
    public static function encrypt($plaintext)
    {
        $ENCRYPTION_KEY = env('ENCRYPT_KEY');
        $cipher = 'AES-256-CBC';
        // 加密算法
        $ivlen = openssl_cipher_iv_length($cipher);
        // 获取初始化向量长度
        $iv = openssl_random_pseudo_bytes($ivlen);
        // 生成随机初始化向量
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $ENCRYPTION_KEY,  OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $ENCRYPTION_KEY, true);
        // 计算 HMAC
        return base64_encode($iv . $hmac . $ciphertext_raw);
        // 对 HMAC 和密文进行编码
    }

    // 解密
    public static function decrypt($ciphertext)
    {
        $cipher = 'AES-256-CBC';
        // 加密算法
        $c = base64_decode($ciphertext);
        // 解码密文
        $ivlen = openssl_cipher_iv_length($cipher);
        // 获取初始化向量长度
        $iv = substr($c, 0, $ivlen);
        // 从密文中提取初始化向量
        $hmac = substr($c, $ivlen, $sha2len = 32);
        // 从密文中提取 HMAC
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        // 从密文中提取加密后的原始数据
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, self::$ENCRYPTION_KEY, OPENSSL_RAW_DATA, $iv);
        // 使用加密密钥和初始化向量解密密文
        $calcmac = hash_hmac('sha256', $ciphertext_raw, self::$ENCRYPTION_KEY, true);
        // 计算 HMAC
        if (hash_equals($hmac, $calcmac)) {
            // 比较 HMAC 是否一致
            return $original_plaintext;
        }
        return null;
    }
}
