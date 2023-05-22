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
use App\Model\DriveClass;
use App\Service\DriveClassService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class DriveClassController extends AbstractController
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
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        // 顯示幾筆
        $step = DriveClass::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = DriveClass::with('user')->whereNull('deleted_at')->offset(($page - 1) * $step)->limit($step)->get();
        $total = DriveClass::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.drive_class_control.drive_class_control');
        $data['drive_class_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/drive_class/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.driveClass.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.drive_class_control.drive_class_insert');
        $data['drive_class_active'] = 'active';
        return $this->render->render('admin.driveClass.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(RequestInterface $request, ResponseInterface $response, DriveClassService $service): PsrResponseInterface
    {
        $data['user_id'] = auth('session')->user()->getId();
        $data['id'] = $request->input('id');
        $data['name'] = $request->input('name');
        $data['description'] = $request->input('description');
        $service->storeDriveClass($data);
        return $response->redirect('/admin/drive_class/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = DriveClass::findOrFail($id);
        $data['navbar'] = trans('default.drive_class_control.drive_class_edit');
        $data['drive_class_active'] = 'active';
        return $this->render->render('admin.driveClass.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'delete')]
    public function delete(RequestInterface $request, ResponseInterface $response, DriveClassService $service): PsrResponseInterface
    {
        $id = (int)$request->input('id');
        $service->deleteDriveClass($id);
        return $response->redirect('/admin/drive_class/index');
    }
}
