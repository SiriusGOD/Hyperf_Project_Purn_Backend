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
namespace App\Controller\Api;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Service\RedeemService;
use App\Service\MemberRedeemService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class RedeemController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'videoRedeem')]
    public function videoRedeem(RequestInterface $request, RedeemService $redeemService)
    {
        $memberId = auth('jwt')->user()->getId();
        $videoId= $request->input("video_id");
        if (empty($videoId)) {
            return $this->error('video id 字段是必须的', ErrorCode::BAD_REQUEST);
        }
        $result = $redeemService->redeemVideo($memberId,$videoId);
        return $this->success(['models' => $result]);
    }

    #[RequestMapping(methods: ['GET'], path: 'videoRedeemList')]
    public function videoRedeemList(RequestInterface $request, MemberRedeemService $memberRedeemService)
    {
        $memberId = auth('jwt')->user()->getId();
        $page = $request->input("page",0);
        $result = $memberRedeemService->getRedeemList($memberId ,$page);
        return $this->success(['models' => $result]);
    }

}
