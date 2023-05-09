<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Controller\AbstractController;

#[Controller]
#[Middleware(ApiAuthMiddleware::class)]
class ReportController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request)
    {
        $data = trans('report.details');
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'create')]
    public function create(RequestInterface $request)
    {
        $userId = auth('jwt')->user()->getId();
        $id = (int) $request->input('id', 0);
        $desc = $request->input('desc', "");
        return $this->success();
    }
}
