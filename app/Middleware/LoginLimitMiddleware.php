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

use Carbon\Carbon;
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
        $now = Carbon::now()->timestamp;
        $tomorrow = Carbon::tomorrow()->setHour(0)->setMinute(0)->setSecond(0)->timestamp;
        $expire = $tomorrow - $now;

        $ip = $this->getIP($request->getServerParams());
        $key = self::LOGIN_LIMIT_CACHE_KEY . $ip;
        $this->logger->info('login limit redis key : ' . $key);

        if ($this->redis->exists($key) and $this->redis->get($key) >= 3) {
            return $this->response->json([
                'code' => 405,
                'msg' => trans('validation.try_limit'),
            ]);
        }

        return $handler->handle($request);
    }

    public function getIP(array $params)
    {
        if (! empty($params['http_client_ip'])) {
            $ip = $params['http_client_ip'];
        } elseif (! empty($params['http_x_forwarded_for'])) {
            $ip = $params['http_x_forwarded_for'];
        } else {
            $ip = $params['remote_addr'];
        }

        return $ip;
    }
}
