<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use Exception;
use App\Constants\ApiCode;
use App\Constants\ErrorCode;
use HyperfExt\Jwt\JwtFactory;
use Hyperf\Di\Annotation\Inject;
use App\Traits\ApiResponseTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HyperfExt\Jwt\Contracts\ManagerInterface;
use HyperfExt\Jwt\Exceptions\TokenExpiredException;

class RefreshTokenMiddleware implements MiddlewareInterface
{
    use ApiResponseTrait;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @Inject
     * @var ManagerInterface
     */
    private ManagerInterface $manager;

    /**
     * @Inject
     * @var JwtFactory
     */
    private JwtFactory $jwtFactory;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwt = $this->jwtFactory->make();
        try{
            $jwt->checkOrFail();
        }catch(Exception $exception){
            if(!$exception instanceof TokenExpiredException){
                return $this->setHttpCode(ErrorCode::UNPROCESSABLE_ENTITY)->fail($exception->getMessage());
            }
            try{
                $token = $jwt->getToken();

                // 刷新token
                $new_token = $jwt->getManager()->refresh($token);

                // 解析token载荷信息
                $payload = $jwt->getManager()->decode($token, false, true);

                // 旧token加入黑名单
                $jwt->getManager()->getBlacklist()->add($payload);

                // 一次性登录，保证此次请求畅通
                auth($payload->get('guard') ?? config('auth.default.guard'))->onceUsingId($payload->get('sub'));

                return $handler->handle($request)->withHeader('authorization', 'bearer ' . $new_token);
            }catch(Exception $exception){
                return $this->setHttpCode(ErrorCode::UNPROCESSABLE_ENTITY)->fail($exception->getMessage());
            }
        }

        return $handler->handle($request);
    }
}
