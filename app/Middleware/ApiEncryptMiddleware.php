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
namespace App\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiEncryptMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $response;

    public function __construct(ContainerInterface $container, HttpResponse $response)
    {
        $this->container = $container;
        $this->response = $response;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $key = env("APP_KEY");
        $signKey = env("SIGN_KEY");
        $attrs = $request->getAttribute('Hyperf\HttpServer\Router\Dispatched');
        $parsedBody = $request->getParsedBody();
        $header = $request->getHeaders();
        $signature = isset($header["headers"]["X-HMAC-Signature"]) ? $header["headers"]["X-HMAC-Signature"] : false;
        if ($signature) {
            // 對加密後的資料進行解密
            $decryptedData = openssl_decrypt(base64_decode($parsedBody['data']), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
            // 重新計算簽名
            $expectedSignature = hash_hmac('sha256', base64_decode($parsedBody['data']), $signKey);
            $service = di(\App\Service\EncryptService::class);
            if ($service->hasPermission($attrs->handler->callback, 
                $parsedBody, 
                $signature, 
                $expectedSignature, 
                $decryptedData )) 
            {
                return $handler->handle($request);
            }
        }
    }
}
