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

use App\Constants\Constants;
use App\Controller\AbstractController;
use App\Model\Navigation;
use App\Model\Tag;
use App\Request\NavigationRequest;
use App\Request\TagRequest;
use App\Service\NavigationService;
use App\Service\TagService;
use Hyperf\DbConnection\Db;
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
class NavigationController extends AbstractController
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
        $step = Constants::DEFAULT_PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = Navigation::select('*')->get();
        $total = Navigation::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.tag_control.tag_control');
        $data['navigation_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/tag/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.navigation.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(NavigationRequest $request, ResponseInterface $response, NavigationService $service): PsrResponseInterface
    {
        $userId = auth('session')->user()->getId();
        $id = $request->input('id');
        $name = $request->input('name');
        $hotOrder = $request->input('hot_order');
        $service->createNavigation([
            'id' => $id,
            'user_id' => $userId,
            'name' => $name,
            'hot_order' => $hotOrder,
        ]);
        return $response->redirect('/admin/navigation/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $data['navbar'] = trans('default.navigation_control.navigation_edit');
        $data['navigation_active'] = 'active';
        $data['model'] = Navigation::find($request->input('id'));
        return $this->render->render('admin.navigation.form', $data);
    }
}
