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
namespace App\Exception\Handler;

use App\Constants\ApiCode;
use App\Exception\UnauthorizedException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\ValidationExceptionHandler;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface;

class ValidationAuthorizeException extends ValidationExceptionHandler
{
    #[Inject]
    protected RenderInterface $render;

    #[Inject]
    protected \Hyperf\HttpServer\Contract\ResponseInterface $response;

    public function handle(\Throwable $throwable, ResponseInterface $response)
    {
        if (! $throwable instanceof UnauthorizedException) {
            return $response;
        }
        $this->stopPropagation();
        $url = request()->getUri()->getPath();
        if (str_contains($url, 'api')) {
            return $this->response->json(['code' => ApiCode::BAD_LOGIN, 'msg' => $throwable->getMessage()]);
        }
        return $this->render->render('error', ['errors' => $throwable->getMessage()]);
    }

    public function isValid(\Throwable $throwable): bool
    {
        return true;
    }
}
