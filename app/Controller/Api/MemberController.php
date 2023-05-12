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
use App\Constants\MemberCode;
use App\Controller\AbstractController;
use App\Job\EmailVerificationJob;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Middleware\TryLimitMiddleware;
use App\Middleware\ApiEncryptMiddleware;
use App\Model\Member;
use App\Model\MemberFollow;
use App\Model\MemberTag;
use App\Model\MemberVerification;
use App\Request\AddFollowerRequest;
use App\Request\AddMemberFollowRequest;
use App\Request\AddMemberTagRequest;
use App\Request\MemberApiUpdateRequest;
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
#[Middleware(ApiEncryptMiddleware::class)]
class MemberController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'login')]
    public function login(RequestInterface $request, MemberService $service, MemberCategorizationService $memberCategorizationService)
    {
        $user = $service->apiGetUser([
            'email' => $request->input('email'),
            'account' => $request->input('account') ?? $request->input('device_id'),
        ]);

        if (! empty($user)) {
            $check = $service->checkPassword($request->input('password', ''), $user->password);
            if (! $check and ! empty($user->password)) {
                return $this->error(trans('validation.password_error'), MemberCode::PAS_ERROR);
            }
        } elseif (! empty($request->input('account')) and ! empty($request->input('device_id'))) {
            return $this->error(trans('validation.authorize'), MemberCode::AUT_ERROR);
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
                'aff_url'=> $request->input('aff_url', ''),
                'invited_code' => $request->input('invited_code', ''),
            ]);

            if (empty($user)) {
                return $this->error(trans('validation.authorize'), MemberCode::AUT_ERROR2);
            }

            $memberCategorizationService->createOrUpdateMemberCategorization([
                'member_id' => $user->id,
                'name' => trans('default.default_categorization_name'),
                'hot_order' => 1,
                'is_default' => 1,
                'is_first' => 1,
            ]);
        }

        // 測試環境先關閉
        if (env('APP_ENV')== 'product') {
            if (! $service->checkAndSaveDevice($user->id, $request->input('device_id'))) {
                return $this->error(trans('validation.authorize'), MemberCode::AUT_ERROR3);
            }
        }

        $token = auth()->login($user);
        // 紀錄登陸ip 與 device
        $base_service = di(\App\Service\BaseService::class);
        $ip = $base_service->getIp($request->getHeaders(), $request->getServerParams());
        $service->updateUser($user->id, [
            'device' => $request->input('device'),
            'last_ip' => $ip,
        ]);
        //登入資數限制
        $loginLimitRes = $service->loginLimit($request->input('device_id'));
        if(is_array($loginLimitRes)){
            return $this->response->json($loginLimitRes);
        }
        $service->createOrUpdateLoginLimitRedisKey($request->input('device_id'));

        $service->saveToken($user->id, $token);
        return $this->success([
            'id' => $user->id,
            'token' => $token,
        ]);
    }

    #[RequestMapping(methods: ['POST'], path: 'logout')]
    public function logout()
    {
        auth()->logout();

        return $this->success();
    }

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'tag')]
    public function addMemberTag(RequestInterface $request)
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
    #[RequestMapping(methods: ['POST'], path: 'update')]
    public function update(RequestInterface $request, MemberService $service)
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
    #[RequestMapping(methods: ['POST'], path: 'detail')]
    public function detail(MemberService $service)
    {
        $id = auth('jwt')->user()->getId();
        $member = $service->getMember($id);
        return $this->success($member);
    }

    #[Middleware(TryLimitMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'verification')]
    public function sendVerification(RequestInterface $request, MemberService $service, DriverFactory $factory)
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
    public function sendResetVerification(RequestInterface $request, MemberService $service, DriverFactory $factory)
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
    public function checkRegisterVerificationCode(RequestInterface $request)
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
    public function checkResetPasswordVerificationCode(RequestInterface $request, MemberService $service)
    {
        $account = $request->input('account');
        if (empty($account)) {
            $account = $request->input('device_id');
        }
        $member = $service->getUserFromAccountOrEmail($account);

        if (empty($member)) {
            return $this->error(trans('validation.exists', ['attribute' => 'device_id or account']), 400);
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
    public function addMemberIdsFollow(RequestInterface $request, MemberService $memberService, MemberFollowService $memberFollowService)
    {
        $follow_ids = $request->input('ids');
        $type = $request->input('type');
        $userId = auth('jwt')->user()->getId();
        $memberService->delRedis($userId);
        $res = $memberFollowService->addTagsFlower($type, $userId, $follow_ids);
        if ($res) {
            return $this->success();
        }
        return $this->error(trans('api.member_control.is_follow'), ErrorCode::BAD_REQUEST);
    }

    #[RequestMapping(methods: ['POST'], path: 'addFollow')]
    public function addMemberFollow(RequestInterface $request, MemberService $service)
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
        
            // 刪除快取
            $service->delFrontCache();

            // 更新會員追蹤演員快取
            $service->updateMemberFollowCache($userId);
            
            return $this->success();
        }

        return $this->error(trans('api.member_control.is_follow'), ErrorCode::BAD_REQUEST);
    }

    #[RequestMapping(methods: ['POST'], path: 'deleteFollow')]
    public function deleteMemberFollow(RequestInterface $request, MemberService $service)
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
            // 刪除快取
            $service->delFrontCache();

            // 更新會員追蹤演員快取
            $service->updateMemberFollowCache($userId);
            return $this->success();
        }
        return $this->error(trans('api.member_control.no_follow_data'), ErrorCode::BAD_REQUEST);
    }
    //追蹤清單
    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'getFollowList')]
    public function getMemberFollowList(RequestInterface $request, MemberService $service)
    {
        $userId = auth('jwt')->user()->getId();
        $follow_type = $request->input('type');
        $page = $request->input('page');
        $limit = $request->input('limit');
        $result = $service->getMemberFollowList($userId, $follow_type, $page ,$limit);
        return $this->success(['models' => $result]);
    }

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'getMemberProductId')]
    public function getMemberProductId(RequestInterface $request, MemberService $service)
    {
        $id = auth('jwt')->user()->getId();
        $type = $request->input('type', 'all');
        $page = $request->input('page', 0);
        $pageSize = $request->input('limit', 20);
        $result = $service->getMemberProductId($id, $type, $page, $pageSize);
        return $this->success(['models' => $result]);
    }

    /**
     * 獲取該使用者的購買清單
     */
    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'getMemberOrderList')]
    public function getMemberOrderList(RequestInterface $request, MemberService $service)
    {
        $user_id = auth('jwt')->user()->getId();
        $page = $request->input('page', 0);
        $limit = $request->input('limit');
        $result = $service->getMemberOrderList($user_id, $page, $limit);
        return $this->success(['models' => $result]);
    }

    /*
     * 獲取推薦列表
     */
    // #[RequestMapping(methods: ['POST'], path: 'getPersonalList')]
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
