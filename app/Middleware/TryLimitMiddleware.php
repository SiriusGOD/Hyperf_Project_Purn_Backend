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

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TryLimitMiddleware implements MiddlewareInterface
{
    public const TRY_LIMIT_CACHE_KEY = 'try_limit:';

    public const TRY_LIMIT_EXPIRE_SECOND = 60;

    protected ContainerInterface $container;

    protected HttpResponse $response;

    protected Redis $redis;

    protected $logger;

    public function __construct(ContainerInterface $container, HttpResponse $response, Redis $redis, LoggerFactory $factory)
    {
        $this->container = $container;
        $this->response = $response;
        $this->redis = $redis;
        $this->logger = $factory->get();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = $this->getIP($request->getServerParams());
        $path = make(RequestInterface::class)->path();
        $key = self::TRY_LIMIT_CACHE_KEY . $path . ':' . $ip;
        $this->logger->info('try limit redis key : ' . $key);

        if ($this->redis->exists($key)) {
            return $this->response->json([
                'code' => 405,
                'msg' => trans('validation.try_limit'),
            ]);
        }

        $this->redis->set($key, 'true', self::TRY_LIMIT_EXPIRE_SECOND);

        return $handler->handle($request);
    }

    public function getIP(array $params)
    {
        if (! empty($params['http_client_ip'])) {
            $ip = $params['http_client_ip'];
        } elseif (! empty($params['http_x_forwarded_for'])) {
            $ip = $params['http_x_forwarded_for'];
        } else {
          if(isset($params['remote_addr'])){
            $ip = $params['remote_addr'];
          }else{
            $ip = '127.0.0.1';

          }
        }

        return $ip;
    }
}
