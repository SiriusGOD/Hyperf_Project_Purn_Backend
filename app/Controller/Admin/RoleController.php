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
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use App\Middleware\PermissionMiddleware;

/**
 * @Controller
 * @Middleware(PermissionMiddleware::class)
 */
class RoleController extends AbstractController
{
    protected RenderInterface $render;

    public function __construct(RenderInterface $render)
    {
        parent::__construct();
        $this->render = $render;
    }

    /**
     * @RequestMapping(path="index", methods={"GET"})
     */
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

    /**
     * @RequestMapping(path="store", methods={"POST"})
     */
    public function store(RequestInterface $request, ResponseInterface $response, RoleService $service, PermissionService $permissionService): PsrResponseInterface
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['name'] = $request->input('name');
        $role = $service->storeRole($data);
        $permissions = $request->input('permissions');
        $permissionService->storePermission($permissions, $role->id);
        return $response->redirect('/admin/role/index');
    }

    /**
     * @RequestMapping(path="create", methods={"get"})
     */
    public function create(PermissionService $permissionService)
    {
        $data['navbar'] = trans('default.role_control.role_insert');
        $data['role'] = new Role();
        $data['role_active'] = 'active';
        $data['rolePermission'] = [];
        $data['permissions'] = $permissionService->parseData();

        return $this->render->render('admin.role.form', $data);
    }

    /**
     * @RequestMapping(path="edit", methods={"get"})
     */
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

    /**
     * @RequestMapping(path="delete", methods={"POST"})
     */
    public function delete(RequestInterface $request, ResponseInterface $response, RoleService $service, UserService $userService): PsrResponseInterface
    {
        $roleId = $request->input('id');
        $service->delRole($roleId);
        $userService->userRoleUpdate($roleId);
        return $response->redirect('/admin/role/index');
    }
}
