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
use App\Model\MemberRedeem;
use App\Model\MemberRedeemVideo;
use App\Service\MemberRedeemService;
use App\Service\MemberRedeemVideoService;
use App\Service\RedeemService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
class RedeemController extends AbstractController
{
    // 檢查兌換碼
    #[RequestMapping(methods: ['POST'], path: 'checkRedeem')]
    public function checkRedeem(RequestInterface $request, RedeemService $redeemService)
    {
        $code = $request->input('code');
        $result = $redeemService->checkRedeem($code);
        if($result ){
          return $this->success(['msg' => "可兌換"]);
        }
        return $this->error(['msg' => "己過期或找不到優惠卷"]);
    }

    // 兌換影片
    #[RequestMapping(methods: ['POST'], path: 'videoRedeem')]
    public function videoRedeem(RequestInterface $request, RedeemService $redeemService)
    {
        $memberId = auth('jwt')->user()->getId();
        $videoId = $request->input('video_id');
        if (empty($videoId)) {
            return $this->error('video id 字段是必须的', ErrorCode::BAD_REQUEST);
        }
        $result = $redeemService->redeemVideo($memberId, $videoId);
        return $this->success(['models' => $result]);
    }
  
    // 兌換影片
    #[RequestMapping(methods: ['POST'], path: 'redeemCode')]
    public function redeemCode(RequestInterface $request, RedeemService $redeemService)
    {
        $memberId = auth('jwt')->user()->getId();
        $code = $request->input('code');
        if (empty($code)) {
            return $this->error('code 字段是必须的', ErrorCode::BAD_REQUEST);
        }
        $result = $redeemService->executeRedeemCode($code, $memberId);
        return $this->success($result);
    }
    // 使用者的兌換卷列表
    #[RequestMapping(methods: ['POST'], path: 'videoRedeemList')]
    public function videoRedeemList(RequestInterface $request, MemberRedeemService $memberRedeemService)
    {
        $memberId = auth('jwt')->user()->getId();
        $page = $request->input('page', 0);
        $result = $memberRedeemService->getRedeemList($memberId, $page);
        $data = [];
        $data['models'] = $result;
        $path = '/api/redeem/videoRedeemList';
        $simplePaginator = new SimplePaginator($page, MemberRedeem::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    // 使用者己兌換列表
    #[RequestMapping(methods: ['POST'], path: 'usedVideoRedeemList')]
    public function usedVideoRedeemList(RequestInterface $request, MemberRedeemVideoService $memberRedeemServiceVideo)
    {
        $memberId = auth('jwt')->user()->getId();
        $page = $request->input('page', 0);
        $result = $memberRedeemServiceVideo->usedRedeemList($memberId, $page);
        $data = [];
        $data['models'] = $result;
        $path = '/api/redeem/usedVideoRedeemList';
        $simplePaginator = new SimplePaginator($page, MemberRedeemVideo::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }
}
