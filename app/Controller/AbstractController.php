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

use App\Constants\ApiCode;
use App\Constants\ErrorCode;
use App\Util\CRYPT;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Container;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

abstract class AbstractController
{
    #[Inject(value: 'Psr\\Container\\ContainerInterface')]
    protected Container $container;

    #[Inject(value: 'Hyperf\\HttpServer\\Contract\\RequestInterface')]
    protected Request $request;

    #[Inject(value: 'Hyperf\\HttpServer\\Contract\\ResponseInterface')]
    protected Response $response;

    public function success(array $data = [], string $message = 'success'): PsrResponseInterface
    {
        $result = ['code' => ApiCode::OK, 'msg' => $message, 'data' => $data];
        if (env('ENCRYPT_FLAG')) {
            $result['data'] = CRYPT::encrypt(json_encode($data));
        }
        return $this->response->json($result);
    }

    public function error(string $message = '', int $code = ErrorCode::SERVER_ERROR): PsrResponseInterface
    {
        return $this->response->json(['code' => $code, 'msg' => $message]);
    }

    public function paginator($total, $data): PsrResponseInterface
    {
        return $this->response->json(['code' => ApiCode::OK, 'data' => ['total' => $total, 'items' => $data]]);
    }
}
