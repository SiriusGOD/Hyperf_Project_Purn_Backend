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

    public function success(array $data = [], string $message = 'success'): PsrResponseInterface
    {
        $data = [
            'code' => ApiCode::OK,
            'msg'  => $message,
            'data' => $data,
        ];

        return $this->response->json($data);
    }

    public function error(string $message = '', int $code = ErrorCode::SERVER_ERROR): PsrResponseInterface
    {
        return $this->response->json(
            [
                'code' => $code,
                'msg'  => $message,
            ]
        );
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
