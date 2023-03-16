<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Request\UserLoginRequest;
use App\Service\UserService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller()
 */
class UserController extends AbstractController
{
    /**
     * @RequestMapping(path="login", methods="post")
     */
    public function login(UserLoginRequest $request, UserService $service)
    {
        $user = $service->checkUser([
            'name' => $request->input('name'),
            'password' => $request->input('password')
        ]);


        if ($user) {
            $token = auth()->login($user);
            return $this->success([
                'token' => $token
            ]);
        }

        return $this->error('',401);
    }
}
