<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Pay;
use App\Service\PayService;
use App\Request\PayStoreRequest;
use App\Controller\AbstractController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Paginator\Paginator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller]
#[Middleware(middleware: 'App\\Middleware\\PermissionMiddleware')]
class PayController extends AbstractController
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
        $step = Pay::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Pay::offset(($page - 1) * $step)->limit($step);
        $pays = $query->get();
        $query = Pay::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.pay_control.pay_control');
        $data['pay_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $pays;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/order/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($pays, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.pay.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'create')]
    public function create()
    {
        $data['navbar'] = trans('default.pay_control.pay_insert_control');
        $data['pay_active'] = 'active';
        return $this->render->render('admin.pay.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'store')]
    public function store(PayStoreRequest $request, ResponseInterface $response, PayService $service): PsrResponseInterface
    {
        $userId = auth('session')->user()->getId();
        $id = $request->input('id', 0);
        $pronoun = $request->input('pronoun');
        $name = $request->input('name');
        $expire = $request->input('expire');
        $service->store([
            'id' => $id,
            'user_id' => $userId,
            'pronoun' => $pronoun,
            'name' => $name,
            'expire' => $expire,
        ]);
        return $response->redirect('/admin/pay/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Pay::findOrFail($id);
        $data['navbar'] = trans('default.pay_control.pay_edit_control');
        $data['pay_active'] = 'active';
        return $this->render->render('admin.pay.form', $data);
    }
}
