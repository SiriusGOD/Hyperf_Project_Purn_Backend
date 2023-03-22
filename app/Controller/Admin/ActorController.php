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
use App\Middleware\PermissionMiddleware;
use App\Model\Actor;
use App\Request\ActorRequest;
use App\Service\ActorService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
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

/**
 * @Controller
 * @Middleware(PermissionMiddleware::class)
 */
class ActorController extends AbstractController
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
        $step = Actor::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Actor::offset(($page - 1) * $step)
            ->limit($step);
        $actors = $query->get();
        $query = Actor::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.actor.title');
        $data['actor_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $actors;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/actor/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($actors, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.actor.index', $data);
    }

    /**
     * @RequestMapping(path="store", methods={"POST"})
     */
    public function store(ActorRequest $request, ResponseInterface $response, ActorService $service): PsrResponseInterface
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['user_id'] = auth('session')->user()->id;
        $data['name'] = $request->input('name');
        $data['sex'] = $request->input('sex');
        $service->storeActor($data);
        return $response->redirect('/admin/actor/index');
    }

    /**
     * @RequestMapping(path="create", methods={"get"})
     */
    public function create()
    {
        $data['navbar'] = trans('default.actor.insert');
        $data['actor_active'] = 'active';
        $model = new Actor();
        $data['model'] = $model;
        return $this->render->render('admin.actor.form', $data);
    }

    /**
     * @RequestMapping(path="edit", methods={"get"})
     */
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Actor::findOrFail($id);
        $data['navbar'] = trans('default.ad_control.ad_update');
        $data['actor_active'] = 'active';
        return $this->render->render('admin.actor.form', $data);
    }

    /**
     * @RequestMapping(path="expire", methods={"POST"})
     */
    public function expire(RequestInterface $request, ResponseInterface $response, ActorService $service): PsrResponseInterface
    {
        $query = Actor::where('id', $request->input('id'));
        $record = $query->first();

        if (empty($record)) {
            return $response->redirect('/admin/actor/index');
        }

        $record->expire = $request->input('expire', 1);
        $record->save();
        $service->updateCache();
        return $response->redirect('/admin/actor/index');
    }
}

