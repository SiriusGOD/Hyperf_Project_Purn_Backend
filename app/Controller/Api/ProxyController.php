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

use App\Controller\AbstractController;
use App\Service\ProxyService;
use App\Service\MemberService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class ProxyController extends AbstractController
{
    // 分享/邀請碼
    #[RequestMapping(methods: ['GET'], path: 'share')]
    public function share(RequestInterface $request, MemberService $memberService)
    {
        $memberId = auth('jwt')->user()->getId();
        $result = $memberService->getMember($memberId);
        return $this->success(['code' => $result['aff'] ]);
    }

}
