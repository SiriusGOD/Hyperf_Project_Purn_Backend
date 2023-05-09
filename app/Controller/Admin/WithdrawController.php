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
use App\Constants\WithdrawCode;
use App\Model\User;
use App\Request\UserUpdateRequest;
use App\Service\RoleService;
use App\Service\UserService;
use App\Service\WithdrawService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class WithdrawController extends AbstractController
{
    protected RenderInterface $render;
    protected $withdrawService;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(RenderInterface $render ,WithdrawService $withdrawService)
    {
        parent::__construct();
        $this->withdrawService = $withdrawService;
        $this->render = $render;
    }

    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request)
    {
        $status  = $request->input('status', WithdrawCode::DEFAULT);
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $users = $this->withdrawService->withdrawList($page, WithdrawCode::PAGE_PER ,$status);
        $total = $this->withdrawService->count($status);
        $data['last_page'] = ceil($total / WithdrawCode::PAGE_PER);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['status'] = WithdrawCode::STATUS;
        $data['total'] = $total;
        $data['datas'] = $users;
        $data['page'] = $page;
        $data['step'] = WithdrawCode::PAGE_PER;
        $path = '/admin/withdraw/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $data['navbar'] = trans('default.withdraw.title');
        $data['withdraw_active'] = 'active';
        return $this->render->render('admin.withdraw.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(UserUpdateRequest $request, ResponseInterface $response, UserService $service): PsrResponseInterface
    {
        $path = '';
        if ($request->hasFile('avatar')) {
            $path = $service->moveUserAvatar($request->file('avatar'));
            $data['avatar'] = $path;
        }
        $service->storeUser($data);
        return $response->redirect('/admin/withdraw/index');
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
        return $this->render->render('admin.withdraw.form', $data);
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
        $data['navbar'] = trans('default.manager_control.manager_update');
        $data['user_active'] = 'active';
        $data['roles'] = $roleService->getAll();
        return $this->render->render('admin.withdraw.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'delete')]
    public function delete(RequestInterface $request, ResponseInterface $response, UserService $service): PsrResponseInterface
    {
        $service->deleteUser($request->input('id'));
        return $response->redirect('/admin/withdraw/index');
    }
}
