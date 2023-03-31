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
use App\Model\Member;
use App\Model\MemberTag;
use App\Request\AddMemberTagRequest;
use App\Request\MemberDetailRequest;
use App\Request\MemberLoginRequest;
use App\Request\MemberRegisterRequest;
use App\Request\MemberUpdateRequest;
use App\Service\MemberService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class MemberController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'login')]
    public function login(MemberLoginRequest $request, MemberService $service)
    {
        $user = $service->apiCheckUser([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'uuid' => $request->input('uuid'),
        ]);

        if (empty($user)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        if (! $service->checkAndSaveDevice($user->id, $request->input('uuid'))) {
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
            'uuid' => $request->input('uuid', null),
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
            'uuid' => $request->input('uuid'),
        ]);

        return $this->success();
    }

    #[RequestMapping(methods: ['GET'], path: 'detail')]
    public function detail(MemberDetailRequest $request)
    {
        $id = $request->input('id');

        return $this->success(Member::find($id)->toArray());
    }
}
