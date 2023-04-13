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
use App\Model\Announcement;
use App\Request\AnnouncementRequest;
use App\Request\TagRequest;
use App\Service\AnnouncementService;
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
class AnnouncementController extends AbstractController
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
    public function index(RequestInterface $request)
    {
        // 顯示幾筆
        $step = Announcement::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = Announcement::with('user')->offset(($page - 1) * $step)->limit($step)->get();
        $total = Announcement::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.announcement_control.announcement_control');
        $data['announcement_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/announcement/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.announcement.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(AnnouncementRequest $request, ResponseInterface $response, AnnouncementService $service): PsrResponseInterface
    {
        $userId = auth('session')->user()->getId();
        $id = $request->input('id');
        $title = $request->input('title');
        $content = $request->input('content');
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $status = $request->input('status');
        $service->store([
            'id' => $id,
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $status,
        ]);
        return $response->redirect('/admin/announcement/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.announcement_control.announcement_insert');
        $data['announcement_active'] = 'active';
        return $this->render->render('admin.announcement.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Announcement::findOrFail($id);
        $data['navbar'] = trans('default.announcement_control.announcement_update');
        $data['announcement_active'] = 'active';
        return $this->render->render('admin.announcement.form', $data);
    }
}
