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
class MemberController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'login')]
    #[Middleware(LoginLimitMiddleware::class)]
    public function login(MemberLoginRequest $request, MemberService $service)
    {
        $user = $service->apiGetUser([
            'email' => $request->input('email'),
            'account' => $request->input('account') ?? $request->input('device_id'),
        ]);

        if (! empty($user)) {
            $check = $service->checkPassword($request->input('password', ''), $user->password);
            if (! $check and ! empty($user->password)) {
                return $this->error(trans('validation.authorize'), 401);
            }
        } elseif (! empty($request->input('account')) and ! empty($request->input('device_id'))) {
            return $this->error(trans('validation.authorize'), 401);
        } else {
            $base_service = di(\App\Service\BaseService::class);
            $ip = $base_service->getIp($request->getHeaders(), $request->getServerParams());
            $user = $service->apiRegisterUser([
                'account' => $request->input('account') ?? $request->input('device_id'),
                'device' => $request->input('device', null),
                'register_ip' => $ip,
                'sex' => $request->input('sex', Member::SEX['DEFAULT']),
                'age' => $request->input('age', 18),
                'email' => $request->input('email', ''),
                'phone' => $request->input('phone', ''),
                'name' => $request->input('name', ''),
                'invited_code' => $request->input('invited_code', ''),
            ]);

            if (empty($user)) {
                return $this->error(trans('validation.authorize'), 401);
            }
        }

        if (! $service->checkAndSaveDevice($user->id, $request->input('device_id'))) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $token = auth()->login($user);
        // 紀錄登陸ip 與 device
        $base_service = di(\App\Service\BaseService::class);
        $ip = $base_service->getIp($request->getHeaders(), $request->getServerParams());
        $service->updateUser($user->id, [
            'device' => $request->input('device'),
            'last_ip' => $ip,
        ]);

        $service->createOrUpdateLoginLimitRedisKey($request->input('device_id'));

        $service->saveToken($user->id, $token);
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

    #[Middleware(ApiAuthMiddleware::class)]
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

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['PUT'], path: 'update')]
    public function update(MemberApiUpdateRequest $request, MemberService $service)
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
            // 'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'account' => $request->input('account'),
        ]);

        return $this->success();
    }

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['GET'], path: 'detail')]
    public function detail(MemberDetailRequest $request)
    {
        // $id = $request->input('id');
        $id = auth('jwt')->user()->getId();

        return $this->success(Member::find($id)->toArray());
    }

    #[Middleware(TryLimitMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'verification')]
    public function sendVerification(SendVerificationRequest $request, MemberService $service, DriverFactory $factory)
    {
        if (auth()->check()) {
            $member = auth()->user();
        } else {
            $member = $service->getUserFromAccountOrEmail($request->input('device_id'));
        }

        if (empty($member)) {
            return $this->error(trans('validation.exists', ['attribute' => 'email or device_id']), 400);
        }

        $code = $service->getVerificationCode($member->id);
        $driver = $factory->get('default');
        $content = trans('email.verification.content', ['code' => $code]);
        $driver->push(new EmailVerificationJob($request->input('email'), trans('email.verification.subject'), $content));

        return $this->success();
    }

    #[Middleware(TryLimitMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'reset_verification')]
    public function sendResetVerification(SendVerificationRequest $request, MemberService $service, DriverFactory $factory)
    {
        if (auth()->check()) {
            $member = auth()->user();
        } else {
            $member = $service->getUserFromAccountOrEmail($request->input('device_id'), $request->input('email'));
        }

        if (empty($member)) {
            return $this->error(trans('validation.exists', ['attribute' => 'email or device_id']), 400);
        }

        $code = $service->getVerificationCode($member->id);
        $driver = $factory->get('default');
        $content = trans('email.reset_verification.content', ['code' => $code, 'account' => $member->account]);
        $driver->push(new EmailVerificationJob($request->input('email'), trans('email.reset_verification.subject'), $content));

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
            $member->status = Member::STATUS['VERIFIED'];
            $member->email = $request->input('email') ?? $member->email;
            $member->save();
            $model->delete();
            return $this->success();
        }

        return $this->error(trans('validation.expire_code'), 400);
    }

    #[RequestMapping(methods: ['POST'], path: 'verification/reset_password_check')]
    public function checkResetPasswordVerificationCode(ResetPasswordVerificationRequest $request, MemberService $service)
    {
        $account = $request->input('account');
        if (empty($account)) {
            $account = $request->input('device_id');
        }
        $member = $service->getUserFromAccountOrEmail($account);

        if (empty($member)) {
            return $this->error(trans('validation.exists', ['attribute' => 'device_id']), 400);
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

    // 追蹤多個標籤
    #[RequestMapping(methods: ['POST'], path: 'addMemberIdsFollow')]
    public function addMemberIdsFollow(AddFollowerRequest $request, MemberFollowService $memberFollowService)
    {
        $follow_ids = $request->input('ids');
        $type = $request->input('type');
        $userId = auth('jwt')->user()->getId();
        $res = $memberFollowService->addTagsFlower($type, $userId, $follow_ids);
        if ($res) {
            return $this->success();
        }
        return $this->error('該會員已追蹤', ErrorCode::BAD_REQUEST);
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

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['GET'], path: 'getMemberProductId')]
    public function getMemberProductId(RequestInterface $request, MemberService $service)
    {
        $id = auth('jwt')->user()->getId();
        $type = $request->input('type', 'all');
        $page = $request->input('page', 0);
        $pageSize = $request->input('pageSize', 20);
        // $offset = $request->input('offset', 0);
        // $limit = $request->input('limit', 0);
        $result = $service->getMemberProductId($id, $type, $page, $pageSize);
        return $this->success(['models' => $result]);
    }

    /*
     * 獲取推薦列表
     */
    // #[RequestMapping(methods: ['GET'], path: 'getPersonalList')]
    // public function getPersonalList(RequestInterface $request, MemberService $service)
    // {
    //     $user_id = auth('jwt')->user()->getId();
    //     $method = $request->input('method', 'recommend');
    //     $offset = $request->input('offset', 0);
    //     $limit = $request->input('limit', 0);
    //     $result = $service->getPersonalList($user_id, $method, $offset, $limit);

    //     return $this->success(['models' => $result]);
    // }
}
