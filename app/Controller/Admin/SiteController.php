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
use App\Model\Site;
use App\Request\SiteRequest;
use App\Service\SiteService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use HyperfExt\Jwt\Contracts\JwtFactoryInterface;
use HyperfExt\Jwt\Contracts\ManagerInterface;
use HyperfExt\Jwt\Jwt;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use App\Middleware\PermissionMiddleware;

/**
 * @Controller
 * @Middleware(PermissionMiddleware::class)
 */
class SiteController extends AbstractController
{
    /**
     * 提供了对 JWT 编解码、刷新和失活的能力。
     */
    protected ManagerInterface $manager;

    /**
     * 提供了从请求解析 JWT 及对 JWT 进行一系列相关操作的能力。
     */
    protected Jwt $jwt;

    protected RenderInterface $render;

    /**
     * @Inject
     */
    protected ValidatorFactoryInterface $validationFactory;

    public function __construct(ManagerInterface $manager, JwtFactoryInterface $jwtFactory, RenderInterface $render)
    {
        parent::__construct();
        $this->manager = $manager;
        $this->jwt = $jwtFactory->make();
        $this->render = $render;
    }

    /**
     * @RequestMapping(path="index", methods={"GET"})
     */
    public function index(RequestInterface $request)
    {
        // 顯示幾筆
        $step = Site::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = Site::offset(($page - 1) * $step)
            ->whereNull('deleted_at')
            ->limit($step)
            ->get();
        $total = Site::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.site_control.site_control');
        $data['site_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/site/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);

        $data['paginator'] = $paginator->toArray();

        return $this->render->render('admin.site.index', $data);
    }

    /**
     * @RequestMapping(path="store", methods={"POST"})
     */
    public function store(SiteRequest $request, ResponseInterface $response, SiteService $service): PsrResponseInterface
    {
        $id = (int) $request->input('id', 0);
        $name = $request->input('name');
        $url = $request->input('url');
        $service->storeSite($id, $url, $name);
        return $response->redirect('/admin/site/index');
    }

    /**
     * @RequestMapping(path="create", methods={"get"})
     */
    public function create()
    {
        $data['navbar'] = trans('default.site_control.site_insert');
        $data['site_active'] = 'active';
        return $this->render->render('admin.site.form', $data);
    }

    /**
     * @RequestMapping(path="edit", methods={"get"})
     */
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Site::findOrFail($id);
        $data['navbar'] = trans('default.site_control.site_update');
        $data['site_active'] = 'active';
        return $this->render->render('admin.site.form', $data);
    }

    /**
     * @RequestMapping(path="delete", methods={"POST"})
     */
    public function delete(RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        Site::find($request->input('id', 0))->delete();
        return $response->redirect('/admin/site/index');
    }
}
