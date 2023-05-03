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
use App\Model\MemberFollow;
use App\Model\MemberTag;
use App\Model\MemberVerification;
use App\Request\AddFollowerRequest;
use App\Request\AddMemberFollowRequest;
use App\Request\AddMemberTagRequest;
use App\Request\MemberApiUpdateRequest;
use App\Request\MemberDetailRequest;
use App\Request\MemberLoginRequest;
use App\Request\RegisterVerificationRequest;
use App\Request\ResetPasswordVerificationRequest;
use App\Request\SendVerificationRequest;
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
    public function create()
    {


        return $this->success();
    }
}
