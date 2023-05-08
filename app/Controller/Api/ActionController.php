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
        $data = [array(
                    'id' => 1,
                    'item' => '播放卡頓'
                ),array(
                    'id' => 2,
                    'item' => '我認為不該出現在這'
                ),array(
                    'id' => 3,
                    'item' => '內容血腥'
                ),array(
                    'id' => 4,
                    'item' => '內容噁心'
                ),array(
                    'id' => 5,
                    'item' => '長得太醜'
                )];
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
