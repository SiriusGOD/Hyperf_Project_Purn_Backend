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

use App\Controller\AbstractController;
use App\Model\User;
use App\Request\UserUpdateRequest;
use App\Service\RoleService;
use App\Service\UserService;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use PragmaRX\Google2FA\Google2FA;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class ManagerController extends AbstractController
{
    protected RenderInterface $render;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(RenderInterface $render, Google2FA $google2FA)
    {
        parent::__construct();
        $this->render = $render;
        $this->google2FA = $google2FA;
    }

    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request, UserService $service, RoleService $roleService)
    {
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $users = $service->getList($page, User::PAGE_PER);
        $total = $service->allCount();
        $data['last_page'] = ceil($total / User::PAGE_PER);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['roles'] = $roleService->getAll()->toArray();
        $data['total'] = $total;
        $data['datas'] = $users;
        $data['page'] = $page;
        $data['step'] = User::PAGE_PER;
        $path = '/admin/manager/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $data['navbar'] = trans('default.manager_control.manager_control');
        $data['user_active'] = 'active';
        return $this->render->render('admin.manager.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(UserUpdateRequest $request, ResponseInterface $response, UserService $service): PsrResponseInterface
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $path = '';
        if ($request->hasFile('avatar')) {
            $path = $service->moveUserAvatar($request->file('avatar'));
            $data['avatar'] = $path;
        }
        $data['name'] = $request->input('name');
        $data['sex'] = $request->input('sex');
        $data['age'] = $request->input('age');
        $data['email'] = $request->input('email' , "a".time()."@gmail.com");
        $data['phone'] = $request->input('phone',"0".time());
        $data['status'] = $request->input('status');
        $data['role_id'] = $request->input('role_id');
        $data['password'] = $request->input('password');
        $service->storeUser($data);
        return $response->redirect('/admin/manager/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create(RoleService $roleService)
    {
        $data['google2fa_url'] = '';
        $data['qrcode_image'] = '';
        $data['navbar'] = trans('default.manager_control.manager_insert');
        $data['user'] = new User();
        $data['roles'] = $roleService->getAll();
        $data['user_active'] = 'active';
        return $this->render->render('admin.manager.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'googleAuth')]
    public function googleAuth(RequestInterface $request, UserService $service, ResponseInterface $response)
    {
        $avatar = $this->google2FA->generateSecretKey();
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['avatar'] = $avatar;
        $service->storeUser($data);
        return $response->redirect('/admin/manager/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request, UserService $service, RoleService $roleService)
    {
        $id = $request->input('id');
        $user = $service->findUser(intval($id));
        $data['user'] = $user;
        $data['google2fa_url'] = '';
        $data['qrcode_image'] = '';
        // 如果 還沒設定GOOGLE 驗證碼
        if (strlen($user->avatar) > 1) {
            $g2faUrl = $this->google2FA->getQRCodeUrl(env('APP_NAME', 'CompanyName'), $user->name, $user->avatar);
            $writer = new Writer(new ImageRenderer(new RendererStyle(320), new ImagickImageBackEnd()));
            $qrcode_image = base64_encode($writer->writeString($g2faUrl));
            $data['qrcode_image'] = $qrcode_image;
        }
        $data['navbar'] = trans('default.manager_control.manager_update');
        $data['user_active'] = 'active';
        $data['roles'] = $roleService->getAll();
        return $this->render->render('admin.manager.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'delete')]
    public function delete(RequestInterface $request, ResponseInterface $response, UserService $service): PsrResponseInterface
    {
        $service->deleteUser($request->input('id'));
        return $response->redirect('/admin/manager/index');
    }
}
