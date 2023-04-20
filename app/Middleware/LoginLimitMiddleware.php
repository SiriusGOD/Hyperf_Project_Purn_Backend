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

class LoginLimitMiddleware implements MiddlewareInterface
{
    public const LOGIN_LIMIT_CACHE_KEY = 'login_limit:';

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
        $deviceId = make(RequestInterface::class)->input('device_id');

        if (empty($deviceId)) {
            return $this->response->json([
                'code' => 403,
                'msg' => trans('validation.required', ['attribute' => 'device_id']),
            ]);
        }

        $key = self::LOGIN_LIMIT_CACHE_KEY . $deviceId;
        $this->logger->info('login limit redis key : ' . $key);

        if ($this->redis->exists($key) and $this->redis->get($key) >= 3) {
            return $this->response->json([
                'code' => 405,
                'msg' => trans('validation.try_limit'),
            ]);
        }

        return $handler->handle($request);
    }
}
