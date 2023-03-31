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
use App\Model\User;
use App\Model\MemberTag;
use App\Request\AddUserTagRequest;
use App\Request\UserDetailRequest;
use App\Request\UserLoginRequest;
use App\Request\UserRegisterRequest;
use App\Request\UserUpdateRequest;
use App\Service\UserService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * @Controller
 */
class UserController extends AbstractController
{
    /**
     * @RequestMapping(path="login", methods="post")
     */
    public function login(UserLoginRequest $request, UserService $service)
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
            'token' => $token
        ]);
    }

    /**
     * @RequestMapping(path="register", methods="post")
     */
    public function register(UserRegisterRequest $request, UserService $service)
    {
        $path = '';
        if ($request->hasFile('avatar')) {
            $path = $service->moveUserAvatar($request->file('avatar'));
        }

        $user = $service->apiRegisterUser([
            'name' => $request->input('name'),
            'password' => $request->input('password'),
            'sex' => $request->input('sex', User::SEX['DEFAULT']),
            'age' => $request->input('age', 18),
            'avatar' => $path,
            'email' => $request->input('email', ''),
            'phone' => $request->input('phone', ''),
            'uuid' => $request->input('uuid', null),
        ]);

        $token = auth()->login($user);
        return $this->success([
            'id' => $user->id,
            'token' => $token
        ]);
    }

    /**
     * @RequestMapping(path="tag", methods="post")
     */
    public function addUserTag(AddUserTagRequest $request)
    {
        $tags = $request->input('tags');
        $userId = auth()->user()->getId();

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

            $model->user_id = $userId;
            $model->tag_id = $tag;
            $model->count = empty($model->count) ? 0 : $model->count++;
            $model->save();
        }

        return $this->success();
    }

    /**
     * @RequestMapping(path="logout", methods="get")
     */
    public function logout()
    {
        auth()->logout();

        return $this->success();
    }

    /**
     * @RequestMapping(path="update", methods="put")
     */
    public function update(UserUpdateRequest $request, UserService $service)
    {
        $userId = auth()->user()->getId();
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

    /**
     * @RequestMapping(path="detail", methods="get")
     */
    public function detail(UserDetailRequest $request)
    {
        $id = $request->input('id');

        return $this->success(M::find($id)->toArray());
    }
}
