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
class ActionController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'getReportItem')]
    public function getReportItem(RequestInterface $request)
    {
        $data = ['播放卡頓','我認為不該出現在這','內容血腥','內容噁心','長得太醜'];
        return $this->success(['models' => $data]);
    }

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'Report')]
    public function Report(RequestInterface $request)
    {
        $userId = auth('jwt')->user()->getId();
        $id = (int) $request->input('id', 0);
        $desc = $request->input('desc', 0);
        return $this->success();
    }
}
