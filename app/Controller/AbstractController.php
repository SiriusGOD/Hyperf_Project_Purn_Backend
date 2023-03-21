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

namespace App\Controller;

use Hyperf\Di\Container;
use App\Constants\ApiCode;
use App\Constants\ErrorCode;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Response;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Middleware\AllowIPMiddleware;
use Hyperf\Logger\LoggerFactory;

/**
 * @Middlewares({
 *     @Middleware(AllowIPMiddleware::class)
 * })
 */
abstract class AbstractController
{
    /**
     * @Inject(ContainerInterface::class)
     * @var Container
     */
    protected $container;

    /**
     * @Inject(RequestInterface::class)
     * @var Request
     */
    protected $request;

    /**
     * @Inject(ResponseInterface::class)
     * @var Response
     */
    protected $response;
    protected $ENCRYPTION_KEY;
    // 定义加密密钥
  
    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('reply');
        $this->ENCRYPTION_KEY = env('ENCRYPT_KEY');
    }

    // 加密函数
    public function encrypt($plaintext) {
        $cipher = 'AES-256-CBC'; // 加密算法
        $ivlen = openssl_cipher_iv_length($cipher); // 获取初始化向量长度
        $iv = openssl_random_pseudo_bytes($ivlen); // 生成随机初始化向量
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $this->ENCRYPTION_KEY, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $this->ENCRYPTION_KEY, $as_binary=true); // 计算 HMAC
        $ciphertext = base64_encode($iv.$hmac.$ciphertext_raw); // 对 HMAC 和密文进行编码
        return $ciphertext;
    }
    //解密
    public function decrypt($ciphertext) {
        $cipher = 'AES-256-CBC'; // 加密算法
        $c = base64_decode($ciphertext); // 解码密文
        $ivlen = openssl_cipher_iv_length($cipher); // 获取初始化向量长度
        $iv = substr($c, 0, $ivlen); // 从密文中提取初始化向量
        $hmac = substr($c, $ivlen, $sha2len=32); // 从密文中提取 HMAC
        $ciphertext_raw = substr($c, $ivlen+$sha2len); // 从密文中提取加密后的原始数据
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $this->ENCRYPTION_KEY, $options=OPENSSL_RAW_DATA, $iv); // 使用加密密钥和初始化向量解密密文
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $this->ENCRYPTION_KEY, $as_binary=true); // 计算 HMAC
        if (hash_equals($hmac, $calcmac)) { // 比较 HMAC 是否一致
            return $original_plaintext;
        }
    }


    public function success(array $data = [], string $message = 'success'): PsrResponseInterface
    {
        $data = [
            'code' => ApiCode::OK,
            'msg'  => $message,
            'data' => $data,
        ];
        if(env('ENCRYPT_FLAG')){
          $en = self::encrypt(json_encode($data));
          //$de = self::decrypt($en);
          return $this->response->json($en);
        }else{
          return $this->response->json($data);
        }
    }

    public function error(string $message = '', int $code = ErrorCode::SERVER_ERROR): PsrResponseInterface
    {
        $data = $this->response->json(
            [
                'code' => $code,
                'msg'  => $message,
            ]
        );

        if(env('ENCRYPT_FLAG')){
          $en = self::encrypt(json_encode($data));
          $de = self::decrypt($en);
          return $this->response->json($en);
        }else{
          return $this->response->json($data);
        }
    }

    public function paginator($total, $data): PsrResponseInterface
    {
        return $this->response->json(
            [
                'code' => ApiCode::OK,
                'data' => [
                    'total' => $total,
                    'items' => $data,
                ],
            ]
        );
    }
}
