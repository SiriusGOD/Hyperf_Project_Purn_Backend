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

use Hyperf\Logger\LoggerFactory;

class ObfuscationService
{
    protected \Psr\Log\LoggerInterface $logger;

    private string $appKey; // 数据签名key

    private string $encryptKey; // 数据加密key st

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->appKey = env('APP_KEY');
        $this->encryptKey = env('ENCRYPT_KEY');
        $this->logger = $loggerFactory->get('reply');
    }

    # 验证签名
    public function check_sign($array): bool
    {
        if (! isset($array['sign']) || $array['sign'] == '') {
            return false;
        }
        $sign = $array['sign'];
        unset($array['sign']);
        return $this->make_sign($array) == $sign;
    }

    /*
     * return sign string
     * Date
     * #签名
     */
    public function make_sign($array): string
    {
        if (empty($array)) {
            return '';
        }
        ksort($array);

        $arr_temp = [];
        foreach ($array as $key => $val) {
            if ($key == 'data') {
                $valTemp = str_replace(' ', '+', $val);
                $arr_temp[] = $key . '=' . $valTemp;
            } else {
                $arr_temp[] = $key . '=' . $val;
            }
        }
        $string = implode('&', $arr_temp);

        $string = $string . $this->appKey;
        # 先sha256签名 在md5签名
        return md5(hash('sha256', $string));
    }

    # @aes-256-cfb 加解密
    # 加密
    public function encrypt($input): string
    {
        return openssl_encrypt($input, 'aes-128-cbc', $this->appKey, 0, $this->encryptKey);
    }

    // 解密
    public function decrypt($input): string
    {
        $input = str_replace(' ', '+', $input);
        return openssl_decrypt($input, 'aes-128-cbc', $this->appKey, 0, $this->encryptKey);
    }

    // 回傳 data 加密
    public function replyData($data = '', $errcode = 0): array
    {
        $return['errcode'] = (int) $errcode;
        $return['timestamp'] = time();
        $return['data'] = $data;
        $msg = '我方，返回数据：' . json_encode($return, JSON_PRETTY_PRINT);
        $this->logger->info($msg);
        if (env('APP_ENV') != 'product') {
            return $return;
        }
        if (! empty($return['data'])) {
            $return['data'] = self::encrypt(json_encode($return['data'], JSON_UNESCAPED_UNICODE));
            $return['sign'] = self::make_sign($return);
            return $return;
        }
        return [];
    }

    /*
     * 验证第三方过来的数据是否合法，以及解密
     */
    public function checkInputData(array $data): array|bool
    {
        if (! self::check_sign($data)) {
            $this->logger->info('我方收到，数据--签名验证失败 : ' . json_encode($data, JSON_PRETTY_PRINT));
            return false;
        }
        $json = self::decrypt($data['data']);
        $this->logger->info('我方收到，解密后的 json 数据 : ' . print_r($json, true));
        return json_decode($json, true);
    }
}
