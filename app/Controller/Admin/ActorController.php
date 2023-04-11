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
use Hyperf\DbConnection\Db;
use App\Model\Actor;
use App\Model\ActorHasClassification;
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
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class ActorController extends AbstractController
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
        $step = Actor::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Actor::leftjoin('actor_has_classifications', 'actor_has_classifications.actor_id','actors.id')
                ->leftjoin('actor_classifications', 'actor_classifications.id', 'actor_has_classifications.actor_classifications_id')
                ->select('actors.*', Db::raw("GROUP_CONCAT(actor_classifications.name SEPARATOR ' , ') as classification "))
                ->groupBy('actors.id')
                ->offset(($page - 1) * $step)->limit($step);
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

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(ActorRequest $request, ResponseInterface $response, ActorService $service): PsrResponseInterface
    {
        $data['id'] = $request->input('id') ? $request->input('id') : null;
        $data['user_id'] = auth('session')->user()->id;
        $data['name'] = $request->input('name');
        $data['sex'] = $request->input('sex');
        $data['classifications'] = $request->input('classifications');
        $service->storeActor($data);
        return $response->redirect('/admin/actor/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.actor.insert');
        $data['actor_active'] = 'active';
        $model = new Actor();
        $data['model'] = $model;
        $data['classification_ids'] = '';
        return $this->render->render('admin.actor.form', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Actor::findOrFail($id);
        $data['navbar'] = trans('default.ad_control.ad_update');
        $data['actor_active'] = 'active';
        $data['classification_ids'] = ActorHasClassification::where('actor_id', $id)->get()->pluck('actor_classifications_id');
        return $this->render->render('admin.actor.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'expire')]
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
