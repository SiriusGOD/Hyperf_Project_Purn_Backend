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
use App\Model\UserSite;
use App\Request\SiteRequest;
use App\Request\UserSiteRequest;
use App\Service\SiteService;
use App\Service\UserSiteService;
use App\Traits\SitePermissionTrait;
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
class UserSiteController extends AbstractController
{
    use SitePermissionTrait;
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
        $step = UserSite::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = UserSite::with('user')->with('site')->offset(($page - 1) * $step)
            ->limit($step);
        $query = $this->attachQueryBuilder($query);
        $models = $query->get();
        $query = UserSite::orderBy('id');
        $query = $this->attachQueryBuilder($query);
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.usersite_control.usersite_control');
        $data['user_site_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/user_site/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);

        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.userSite.index', $data);
    }

    /**
     * @RequestMapping(path="store", methods={"POST"})
     */
    public function store(UserSiteRequest $request, ResponseInterface $response, UserSiteService $service): PsrResponseInterface
    {
        $id = (int) $request->input('id', 0);
        $userId = (int) $request->input('user_id');
        $siteId = (int) $request->input('site_id');
        $service->storeUserSite($id, $userId, $siteId);
        return $response->redirect('/admin/user_site/index');
    }

    /**
     * @RequestMapping(path="create", methods={"get"})
     */
    public function create()
    {
        $data['navbar'] = trans('default.usersite_control.usersite_insert');
        $data['user_site_active'] = 'active';
        return $this->render->render('admin.userSite.form', $data);
    }

    /**
     * @RequestMapping(path="edit", methods={"get"})
     */
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = UserSite::findOrFail($id);
        $data['navbar'] = trans('default.usersite_control.usersite_update');
        $data['user_site_active'] = 'active';
        return $this->render->render('admin.userSite.form', $data);
    }

    /**
     * @RequestMapping(path="delete", methods={"POST"})
     */
    public function delete(RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        $query = UserSite::where('id', $request->input('id', 0));
        $query = $this->attachQueryBuilder($query);
        $query->delete();
        return $response->redirect('/admin/user_site/index');
    }
}
