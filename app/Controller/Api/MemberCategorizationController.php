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
use App\Job\EmailVerificationJob;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Middleware\LoginLimitMiddleware;
use App\Middleware\TryLimitMiddleware;
use App\Model\Member;
use App\Model\MemberCategorizationDetail;
use App\Model\MemberFollow;
use App\Model\MemberTag;
use App\Model\MemberVerification;
use App\Request\AddFollowerRequest;
use App\Request\AddMemberFollowRequest;
use App\Request\AddMemberTagRequest;
use App\Request\MemberApiUpdateRequest;
use App\Request\MemberCategorizationCreateRequest;
use App\Request\MemberCategorizationDetailCreateRequest;
use App\Request\MemberDetailRequest;
use App\Request\MemberLoginRequest;
use App\Request\RegisterVerificationRequest;
use App\Request\ResetPasswordVerificationRequest;
use App\Request\SendVerificationRequest;
use App\Service\MemberCategorizationService;
use App\Service\MemberFollowService;
use App\Service\MemberService;
use Carbon\Carbon;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
#[Middleware(ApiAuthMiddleware::class)]
class MemberCategorizationController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'create')]
    public function create(MemberCategorizationCreateRequest $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $service->createMemberCategorization([
            'name' => $request->input('name'),
            'member_id' => $memberId,
            'hot_order' => $request->input('hot_order'),
            'is_default' => $request->input('is_default'),
        ]);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/create')]
    public function createDetail(MemberCategorizationDetailCreateRequest $request, MemberCategorizationService $service)
    {
        $service->createMemberCategorizationDetail([
            'member_categorization_id' => $request->input('id'),
            'type' => MemberCategorizationDetail::TYPES[$request->input('type')],
            'type_id' => $request->input('type_id'),
        ]);

        return $this->success();
    }
}
