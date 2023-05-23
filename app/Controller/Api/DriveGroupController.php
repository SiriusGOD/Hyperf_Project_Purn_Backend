<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;
use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Service\DriveGroupService;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
class DriveGroupController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(DriveGroupService $service)
    {
        $result = $service->getList();
        return $this->success(['models' => $result]);
    }
}
