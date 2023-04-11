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
namespace App\Middleware\Auth;

use App\Service\MemberService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiAuthMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container, HttpResponse $response)
    {
        $this->container = $container;
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $redis = make(Redis::class);

        if (! auth('jwt')->check()) {
            throw new \App\Exception\UnauthorizedException(403, trans('validation.authorize'));
        }

        $token = $redis->get(MemberService::CACHE_KEY . auth('jwt')->user()->getId());

        $headerAuth = make(RequestInterface::class)->header('Authorization');
        if ($headerAuth == 'Bearer ' . $token) {
            return $handler->handle($request);
        }

        throw new \App\Exception\UnauthorizedException(403, trans('validation.authorize'));
    }
}
