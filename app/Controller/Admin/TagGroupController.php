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
use App\Model\TagGroup;
use App\Request\TagGroupRequest;
use App\Service\TagGroupService;
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
class TagGroupController extends AbstractController
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
        $step = TagGroup::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = TagGroup::with('user')->offset(($page - 1) * $step)->limit($step)->get();
        $total = TagGroup::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.tag_group_control.tag_group_control');
        $data['tag_group_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/tag_group/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.tagGroup.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.tag_group_control.tag_group_insert');
        $data['tag_group_active'] = 'active';
        return $this->render->render('admin.tagGroup.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(TagGroupRequest $request, ResponseInterface $response, TagGroupService $service): PsrResponseInterface
    {
        $data['user_id'] = auth('session')->user()->getId();
        $data['id'] = $request->input('id');
        $data['name'] = $request->input('name');
        $data['is_hide'] = $request->input('is_hide', 0);
        $service->storeTagGroup($data);
        return $response->redirect('/admin/tag_group/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = TagGroup::findOrFail($id);
        $data['navbar'] = trans('default.tag_group_control.tag_group_edit');
        $data['tag_group_active'] = 'active';
        return $this->render->render('admin.tagGroup.form', $data);
    }
}
