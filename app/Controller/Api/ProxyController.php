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
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Service\MemberService;
use App\Service\ProxyService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class ProxyController extends AbstractController
{
    // 分享/邀請碼
    #[RequestMapping(methods: ['POST'], path: 'share')]
    public function share(MemberService $memberService)
    {
        $memberId = auth('jwt')->user()->getId();
        $result = $memberService->getMember($memberId);
        return $this->success(['code' => $result['aff']]);
    }

    // 我的錢包
    #[RequestMapping(methods: ['POST'], path: 'wallet')]
    public function wallet(ProxyService $proxyService, MemberService $memberService)
    {
        $memberId = auth('jwt')->user()->getId();
        $data = [];
        $data['models']['proxy'] = $proxyService->downlintTotal($memberId);
        $data['models']['coins'] = $memberService->getMemberSimple($memberId ,["coins"])->coins;
        $data['models']['income'] = $proxyService->incomeTotal($memberId);
        return $this->success($data);
    }
    // 我的收益
    #[RequestMapping(methods: ['POST'], path: 'myIncome')]
    public function myIncome(RequestInterface $request, ProxyService $proxyService)
    {
        $memberId = auth('jwt')->user()->getId();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', ProxyService::LIMIT);
        $result = $proxyService->myIncome($memberId, $page);
        $data = [];
        $data['models'] = $result;
        $path = '/api/proxy/myIncome';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    // 我的代理成員/下線
    #[RequestMapping(methods: ['POST'], path: 'downline')]
    public function downline(RequestInterface $request, ProxyService $proxyService)
    {
        $memberId = auth('jwt')->user()->getId();
        $page = $request->input('page', 1);
        $result = $proxyService->downline($memberId, $page);
        $data = [];
        $data['models'] = $result;
        $path = '/api/proxy/downline';
        $simplePaginator = new SimplePaginator($page, ProxyService::LIMIT, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }
}
