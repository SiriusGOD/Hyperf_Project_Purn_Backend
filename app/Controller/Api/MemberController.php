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
use App\Middleware\TryLimitMiddleware;
use App\Model\Member;
use App\Model\MemberFollow;
use App\Model\MemberTag;
use App\Model\MemberVerification;
use App\Request\AddMemberFollowRequest;
use App\Request\AddMemberTagRequest;
use App\Request\MemberDetailRequest;
use App\Request\MemberLoginRequest;
use App\Request\MemberRegisterRequest;
use App\Request\MemberUpdateRequest;
use App\Request\RegisterVerificationRequest;
use App\Request\ResetPasswordVerificationRequest;
use App\Request\SendVerificationRequest;
use App\Service\MemberService;
use Carbon\Carbon;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class MemberController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'login')]
    public function login(MemberLoginRequest $request, MemberService $service)
    {
        $user = $service->apiCheckUser([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'account' => $request->input('account'),
        ]);

        if (empty($user)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        if (! $service->checkAndSaveDevice($user->id, $request->input('device_id'))) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $token = auth()->login($user);
        $service->saveToken($user->id, $token);
        return $this->success([
            'id' => $user->id,
            'token' => $token,
        ]);
    }

    #[RequestMapping(methods: ['POST'], path: 'register')]
    public function register(MemberRegisterRequest $request, MemberService $service)
    {
        $path = '';
        if ($request->hasFile('avatar')) {
            $path = $service->moveUserAvatar($request->file('avatar'));
        }

        $user = $service->apiRegisterUser([
            'name' => $request->input('name'),
            'password' => $request->input('password'),
            'sex' => $request->input('sex', Member::SEX['DEFAULT']),
            'age' => $request->input('age', 18),
            'avatar' => $path,
            'email' => $request->input('email', ''),
            'phone' => $request->input('phone', ''),
            'account' => $request->input('account', null),
        ]);

        $token = auth()->login($user);
        return $this->success([
            'id' => $user->id,
            'token' => $token,
        ]);
    }

    #[RequestMapping(methods: ['GET'], path: 'logout')]
    public function logout()
    {
        auth()->logout();

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'tag')]
    public function addMemberTag(AddMemberTagRequest $request)
    {
        $tags = $request->input('tags');
        $userId = auth('jwt')->user()->getId();
        foreach ($tags as $tag) {
            if (! is_int($tag)) {
                continue;
            }

            $model = MemberTag::where('member_id', $userId)
                ->where('tag_id', $tag)
                ->first();

            if (empty($model)) {
                $model = new MemberTag();
            }

            $model->member_id = $userId;
            $model->tag_id = $tag;
            $model->count = empty($model->count) ? 1 : $model->count++;
            $model->save();
        }
        return $this->success();
    }

    #[RequestMapping(methods: ['PUT'], path: 'update')]
    public function update(MemberUpdateRequest $request, MemberService $service)
    {
        $userId = auth('jwt')->user()->getId();
        $path = '';
        if ($request->hasFile('avatar')) {
            $path = $service->moveUserAvatar($request->file('avatar'));
        }

        $service->updateUser($userId, [
            'name' => $request->input('name'),
            'password' => $request->input('password'),
            'sex' => $request->input('sex'),
            'age' => $request->input('age'),
            'avatar' => $path,
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'account' => $request->input('account'),
        ]);

        return $this->success();
    }

    #[RequestMapping(methods: ['GET'], path: 'detail')]
    public function detail(MemberDetailRequest $request)
    {
        $id = $request->input('id');

        return $this->success(Member::find($id)->toArray());
    }

    #[Middleware(TryLimitMiddleware::class)]
    #[RequestMapping(methods: ['GET'], path: 'verification')]
    public function sendVerification(SendVerificationRequest $request, MemberService $service, DriverFactory $factory)
    {
        if (auth()->check()) {
            $member = auth()->user();
        } else {
            $member = $service->getUserFromEmailOrUuid($request->input('email'), $request->input('uuid'));
        }

        $code = $service->getVerificationCode($member->id);
        $driver = $factory->get('default');
        $content = trans('email.verification.content', ['code' => $code]);
        $driver->push(new EmailVerificationJob($member->email, $content));

        return $this->success();
    }

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'verification/register_check')]
    public function checkRegisterVerificationCode(RegisterVerificationRequest $request)
    {
        $member = auth()->user();
        $now = Carbon::now()->toDateTimeString();
        $model = MemberVerification::where('member_id', $member->id)
            ->where('expired_at', '>=', $now)
            ->where('code', $request->input('code'))
            ->first();

        if (! empty($model)) {
            $member->status = Member::STATUS['NORMAL'];
            $member->save();
            $model->delete();
            return $this->success();
        }

        return $this->error(trans('validation.expire_code'), 400);
    }

    #[RequestMapping(methods: ['POST'], path: 'verification/reset_password_check')]
    public function checkResetPasswordVerificationCode(ResetPasswordVerificationRequest $request, MemberService $service)
    {
        $member = $service->getUserFromEmailOrUuid($request->input('email'), $request->input('uuid'));

        if (empty($member)) {
            return $this->error(trans('validation.exists', ['attribute' => 'email or uuid']), 400);
        }

        $now = Carbon::now()->toDateTimeString();
        $model = MemberVerification::where('member_id', $member->id)
            ->where('expired_at', '>=', $now)
            ->where('code', $request->input('code'))
            ->first();

        if (! empty($model)) {
            $member->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
            $member->save();
            $model->delete();
            return $this->success();
        }

        return $this->error(trans('validation.expire_code'), 400);
    }

    #[RequestMapping(methods: ['POST'], path: 'addFollow')]
    public function addMemberFollow(AddMemberFollowRequest $request)
    {
        $follow_id = $request->input('id');
        $follow_type = $request->input('type');
        $userId = auth('jwt')->user()->getId();
        $model = MemberFollow::where('member_id', $userId)
            ->where('correspond_type', MemberFollow::TYPE_CORRESPOND_LIST[$follow_type])
            ->where('correspond_id', $follow_id)
            ->whereNull('deleted_at')
            ->first();
        if (empty($model)) {
            $model = new MemberFollow();
            $model->member_id = $userId;
            $model->correspond_type = MemberFollow::TYPE_CORRESPOND_LIST[$follow_type];
            $model->correspond_id = $follow_id;
            $model->save();
            return $this->success();
        }

        return $this->error('該會員已追蹤', ErrorCode::BAD_REQUEST);
    }

    #[RequestMapping(methods: ['POST'], path: 'deleteFollow')]
    public function deleteMemberFollow(AddMemberFollowRequest $request)
    {
        $userId = auth('jwt')->user()->getId();
        $follow_type = $request->input('type');
        $follow_id = $request->input('id');

        $model = MemberFollow::where('member_id', $userId)
            ->where('correspond_type', MemberFollow::TYPE_CORRESPOND_LIST[$follow_type])
            ->where('correspond_id', $follow_id)
            ->whereNull('deleted_at')
            ->first();
        if (! empty($model)) {
            $model->deleted_at = Carbon::now();
            $model->save();
            return $this->success();
        }

        return $this->error('查無該會員追蹤資料', ErrorCode::BAD_REQUEST);
    }

    #[RequestMapping(methods: ['GET'], path: 'getFollowList')]
    public function getMemberFollowList(RequestInterface $request, MemberService $service)
    {
        $userId = auth('jwt')->user()->getId();
        $follow_type = $request->input('type');
        $result = $service->getMemberFollowList($userId, $follow_type);
        return $this->success(['models' => $result]);
    }
}
