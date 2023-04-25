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
    #[RequestMapping(methods: ['POST'], path: 'share')]
    public function share(MemberService $memberService)
    {
        $memberId = auth('jwt')->user()->getId();
        $result = $memberService->getMember($memberId);
        return $this->success(['code' => $result['aff'] ]);
    }

    // 我的收益
    #[RequestMapping(methods: ['POST'], path: 'myIncome')]
    public function myIncome(RequestInterface $request, ProxyService $proxyService)
    {
        $memberId = auth('jwt')->user()->getId();
        $page = $request->input('page',1);
        $result = $proxyService->myIncome($memberId ,$page);
        return $this->success(['models' => $result ]);
    }

    // 我的代理成員/下線
    #[RequestMapping(methods: ['POST'], path: 'downline')]
    public function downline(RequestInterface $request, ProxyService $proxyService)
    {
        $memberId = auth('jwt')->user()->getId();
        $page = $request->input('page',1);
        $result = $proxyService->downline($memberId ,$page);
        return $this->success(['models' => $result ]);
    }
}
