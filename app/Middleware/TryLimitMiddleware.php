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
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TryLimitMiddleware implements MiddlewareInterface
{
    public const TRY_LIMIT_CACHE_KEY = 'try_limit:';

    public const TRY_LIMIT_EXPIRE_SECOND = 180;

    protected ContainerInterface $container;

    protected HttpResponse $response;

    protected Redis $redis;

    public function __construct(ContainerInterface $container, HttpResponse $response, Redis $redis)
    {
        $this->container = $container;
        $this->response = $response;
        $this->redis = $redis;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = make(RequestInterface::class)->path();
        $memberId = auth()->user()->getId();
        $key = self::TRY_LIMIT_CACHE_KEY . $path . ':' . $memberId;

        if ($this->redis->exists($key)) {
            return $this->response->json([
                'code' => 405,
                'msg' => trans('validation.try_limit'),
            ]);
        }

        $this->redis->set($key, 'true', self::TRY_LIMIT_EXPIRE_SECOND);

        return $handler->handle($request);
    }
}
