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

namespace App\Controller\Admin;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Exception\BusinessException;
use App\Middleware\AllowIPMiddleware;
use App\Model\User;
use App\Service\UserService;
use App\Service\PermissionService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use HyperfExt\Hashing\Hash;
use HyperfExt\Jwt\Contracts\JwtFactoryInterface;
use HyperfExt\Jwt\Contracts\ManagerInterface;
use HyperfExt\Jwt\Jwt;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use PragmaRX\Google2FA\Google2FA;

/**
 * @Controller
 * @Middleware(AllowIPMiddleware::class)
 */
class UserController extends AbstractController
{
    /**
     * 提供了对 JWT 编解码、刷新和失活的能力。
     */
    protected ManagerInterface $manager;

    /**
     * 提供了从请求解析 JWT 及对 JWT 进行一系列相关操作的能力。
     */
    protected Jwt $jwt;

    /**
     * @Inject
     */
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(ManagerInterface $manager, JwtFactoryInterface $jwtFactory, PermissionService $permissionService)
    {
        parent::__construct();
        $this->manager = $manager;
        $this->jwt = $jwtFactory->make();
        $this->permissionService = $permissionService;
    }

    /**
     * register.
     * @RequestMapping(path="register", methods={"POST"})
     */
    public function register(): PsrResponseInterface
    {
        return $this->success();
    }

    /**
     * register.
     * @RequestMapping(path="loginPage", methods={"get"})
     */
    public function loginPage(RenderInterface $render)
    {
        return $render->render('loginPage');
    }

    /**
     * @RequestMapping(path="login", methods={"POST"})
     * @return PsrResponseInterface|ResponseInterface
     */
    public function login(RequestInterface $request, ResponseInterface $response, UserService $service, RenderInterface $render, Google2FA $google2FA)
    {
        $input = $this->request->all();
        $validator = $this->validationFactory->make(
            $input,
            [
                'name' => 'required',
                'password' => 'required',
            ],
            [
                'name.required' => 'name is required',
                'password.required' => 'password is required',
            ]
        );
        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            throw new BusinessException(ErrorCode::FORBIDDEN, $errorMessage);
        }
        $credentials = $request->inputs(['name', 'password']);
        $user = $service->checkUser($credentials);
        if ($user) {
            if (env('GOOGLE_AUTH_VALID')) {
                $secret = $request->input('secret');
                $valid = $google2FA->verifyKey($user->avatar, $secret);
                if ($valid == 1) {
                    return $this->handleLogin($user, $response);
                }
            }else{
                return $this->handleLogin($user, $response);
            }
        }
        $data = [
            'error_login' => true,
            'error_login_msg' => trans('default.error_login_msg'),
        ];
        return $render->render('loginPage', $data);
    }

    //登入成功處理
    public function handleLogin($user, $response)
    {
        auth('session')->login($user);
        $this->permissionService->resetPermission();
        return $response->redirect('/admin/index/dashboard');
    }

    /**
     * @RequestMapping(path="logout", methods={"get"})
     */
    public function logout(ResponseInterface $response): PsrResponseInterface
    {
        auth('session')->logout();
        return $response->redirect('/admin/user/loginPage');
    }

    /**
     * @RequestMapping(path="page", methods={"GET"})
     * @param mixed $page
     * @param mixed $step
     */
    public function page($page = 1, $step = 10): PsrResponseInterface
    {
        $data = User::select()->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $step)
            ->limit($step)
            ->get();
        $total = User::count();

        return $this->paginator($total, $data);
    }

    /**
     * @RequestMapping(path="create", methods={"POST"})
     */
    public function create(RequestInterface $request): PsrResponseInterface
    {
        if ($request->input('id')) {
            $record = User::findOrFail($request->input('id'));
        } else {
            $record = new User();
        }
        if (!empty($password = $request->input('password'))) {
            $record->password = Hash::make($password);
        }
        $record->name = $request->input('name');
        $record->phone = $request->input('phone');
        $record->email = $request->input('email');
        $record->sex = $request->input('sex');
        $record->age = $request->input('age');
        $record->status = $request->input('status');
        $record->role_id = $request->input('role_id');
        $record->save();
        return $this->success();
    }

    /**
     * @RequestMapping(path="delete", methods={"DELETE"})
     */
    public function delete(RequestInterface $request): PsrResponseInterface
    {
        $record = User::findOrFail($request->input('id'));
        $record->status = User::STATUS_DELETE;
        $record->save();
        return $this->success();
    }

    /**
     * @param mixed $token
     * @return ResponseInterface
     */
    protected function respondWithToken($token): PsrResponseInterface
    {
        return $this->success(
            [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expire_in' => make(JwtFactoryInterface::class)->make()->getPayloadFactory()->getTtl(),
            ]
        );
    }
}
