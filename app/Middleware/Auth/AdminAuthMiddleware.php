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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use HyperfExt\Jwt\Jwt;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminAuthMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected HttpResponse $response;

    protected Jwt $jwt;

    public function __construct(HttpResponse $response, JWT $jwt)
    {
        $this->response = $response;
        $this->jwt = $jwt;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $this->jwt->checkOrFail();
            $user = auth('api')->user();
            if (empty($user)) {
                throw new BusinessException(ErrorCode::SERVER_ERROR, 'sorry，no user.');
            }
            $request = Context::get(ServerRequestInterface::class);
            // 更改上下文，写入用户ID
            $request = $request->withAttribute('user_id', $user->id);
            Context::set(ServerRequestInterface::class, $request);
            return $handler->handle($request);
        } catch (\Exception $exception) {
            throw new BusinessException(ErrorCode::TOKEN_INVALID, '对不起，token验证没有通过');
        }
    }
}
