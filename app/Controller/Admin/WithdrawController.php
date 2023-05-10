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

    #[RequestMapping(methods: ['GET'], path: 'detail')]
    public function detail(UserUpdateRequest $request): PsrResponseInterface
    {
        $id = $request->input('id');
        $users = $this->withdrawService->get($id);
        $data['last_page'] = ceil($total / WithdrawCode::PAGE_PER);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['status'] = WithdrawCode::STATUS;
        $data['total'] = $total;
        $data['datas'] = $users;
        $data['page'] = $page;
        $data['step'] = WithdrawCode::PAGE_PER;
        $data['navbar'] = trans('default.withdraw.detail');
        $data['withdraw_active'] = 'active';
        return $this->render->render('admin.withdraw.index', $data);
    }


    #[RequestMapping(methods: ['GET'], path: 'set')]
    public function set(RequestInterface $request, ResponseInterface $response, WithdrawService $withdrawService)
    {
        $all = $request->all();
        $withdrawService->setWithDraw($all);
        return $response->redirect('/admin/withdraw/index');
    }
}
