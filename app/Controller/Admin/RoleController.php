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
use App\Model\Role;
use App\Model\User;
use App\Service\PermissionService;
use App\Service\RoleService;
use App\Service\UserService;
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
class RoleController extends AbstractController
{
    protected RenderInterface $render;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(RenderInterface $render)
    {
        parent::__construct();
        $this->render = $render;
    }

    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request, RoleService $service)
    {
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $users = $service->getAll($page, User::PAGE_PER);
        $total = $service->allCount();
        $data['last_page'] = ceil($total / User::PAGE_PER);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['total'] = $total;
        $data['datas'] = $users;
        $data['page'] = $page;
        $data['step'] = 10;
        $path = '/admin/role/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $data['navbar'] = trans('default.role_control.role');
        $data['role_active'] = 'active';
        return $this->render->render('admin.role.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(RequestInterface $request, ResponseInterface $response, RoleService $service, PermissionService $permissionService): PsrResponseInterface
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['name'] = $request->input('name');
        $data['type'] = $request->input('type', 0);
        $role = $service->storeRole($data);
        $permissions = $request->input('permissions');
        $permissionService->storePermission($permissions, $role->id);
        return $response->redirect('/admin/role/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create(PermissionService $permissionService)
    {
        $data['navbar'] = trans('default.role_control.role_insert');
        $data['role'] = new Role();
        $data['role_active'] = 'active';
        $data['rolePermission'] = [];
        $data['permissions'] = $permissionService->parseData();
        return $this->render->render('admin.role.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request, RoleService $service, PermissionService $permissionService)
    {
        $id = $request->input('id');
        $data['role'] = $service->findRole(intval($id));
        $data['navbar'] = trans('default.role_control.role_update');
        $data['role_active'] = 'active';
        $data['rolePermission'] = $permissionService->getRolePermission($id);
        $data['permissions'] = $permissionService->parseData();
        return $this->render->render('admin.role.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'delete')]
    public function delete(RequestInterface $request, ResponseInterface $response, RoleService $service, UserService $userService): PsrResponseInterface
    {
        $roleId = $request->input('id');
        $service->delRole($roleId);
        $userService->userRoleUpdate($roleId);
        return $response->redirect('/admin/role/index');
    }
}
