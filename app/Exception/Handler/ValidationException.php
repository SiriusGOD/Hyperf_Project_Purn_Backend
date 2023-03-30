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
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\ValidationExceptionHandler;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface;

class ValidationException extends ValidationExceptionHandler
{
    #[Inject]
    protected RenderInterface $render;

    #[Inject]
    protected \Hyperf\HttpServer\Contract\ResponseInterface $response;

    public function handle(\Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        /** @var \Hyperf\Validation\ValidationException $throwable */
        $errors = $throwable->validator->errors()->all();
        $url = request()->getUri()->getPath();
        if (str_contains($url, 'api')) {
            return $this->response->json(['code' => ApiCode::BAD_REQUEST, 'msg' => $errors]);
        }
        return $this->render->render('error', ['errors' => $errors]);
    }
}
