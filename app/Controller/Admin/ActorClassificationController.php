<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\View\RenderInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;
use App\Request\ActorClassificationRequest;
use App\Model\ActorClassification;
use App\Service\ActorClassificationService;
use Hyperf\Paginator\Paginator;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class ActorClassificationController extends AbstractController
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
        $step = ActorClassification::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = ActorClassification::with('user')->offset(($page - 1) * $step)->limit($step)->get();
        $total = ActorClassification::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.actor_classification_control.classification_control');
        $data['actor_classification_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/tag/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.actorClassification.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.actor_classification_control.classification_create');
        $data['actor_classification_active'] = 'active';
        return $this->render->render('admin.actorClassification.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(ActorClassificationRequest $request, ResponseInterface $response, ActorClassificationService $service): PsrResponseInterface
    {
        $userId = auth('session')->user()->getId();
        $name = $request->input('name');
        $service->storeActorClassification($name, $userId);
        return $response->redirect('/admin/actorClassification/index');
    }
}
