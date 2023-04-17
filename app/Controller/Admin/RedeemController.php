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

use App\Constants\RedeemCode;
use App\Controller\AbstractController;
use App\Model\Redeem;
use App\Model\Role;
use App\Service\RedeemService;
use App\Util\URand;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class RedeemController extends AbstractController
{
    protected RenderInterface $render;

    public function __construct(RenderInterface $render)
    {
        parent::__construct();
        $this->render = $render;
    }

    #[RequestMapping(methods: ['GET'], path: 'index')]
    public function index(RequestInterface $request, RedeemService $redeemService)
    {
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $redeems = $redeemService->redeemList($page);
        $total = $redeemService->allCount();
        $data['last_page'] = ceil($total / Redeem::PAGE_PER);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['total'] = $total;
        $data['datas'] = $redeems;
        $data['page'] = $page;
        $data['step'] = 10;
        $data['category'] = RedeemCode::CATEGORY;
        $path = '/admin/redeem/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $data['navbar'] = trans('default.redeem.title');
        $data['redeem_active'] = 'active';
        return $this->render->render('admin.redeem.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(RequestInterface $request, ResponseInterface $response, RedeemService $redeemService): PsrResponseInterface
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $all = $request->all();
        $res = $redeemService->store($all);
        if ($res == false) {
            return $response->redirect('/admin/redeem/create');
        }
        return $response->redirect('/admin/redeem/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.redeem.insert');
        $data['role'] = new Role();
        $data['code'] = URand::randomString(10);
        $data['redeem_active'] = 'active';
        $data['rolePermission'] = [];
        return $this->render->render('admin.redeem.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request, RedeemService $redeemService)
    {
        $id = $request->input('id');
        $data['model'] = $redeemService->find((int) $id);
        $data['navbar'] = trans('default.redeem.edit');
        $data['redeem_active'] = 'active';
        return $this->render->render('admin.redeem.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'delete')]
    public function delete(RequestInterface $request, ResponseInterface $response, RedeemService $redeemService): PsrResponseInterface
    {
        $id = $request->input('id');
        $redeemService->updateStatus($id, 1);
        return $response->redirect('/admin/redeem/index');
    }
}
