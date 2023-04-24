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
use App\Model\CustomerService;
use App\Model\CustomerServiceDetail;
use App\Request\CustomerServiceReplyRequest;
use App\Service\CustomerServiceService;
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
class CustomerServiceController extends AbstractController
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
        $step = CustomerService::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = CustomerService::with('member')
            ->offset(($page - 1) * $step)->limit($step)->get();
        $total = CustomerService::count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.customer_service_control.customer_service_control');
        $data['customer_service_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/customer_service/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.customerService.index', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'reply')]
    public function reply(CustomerServiceReplyRequest $request, ResponseInterface $response, CustomerServiceService $service): PsrResponseInterface
    {
        $userId = auth('session')->user()->getId();
        $id = (int) $request->input('id');
        $message = $request->input('message');
        $service->reply([
            'id' => $id,
            'user_id' => $userId,
            'message' => $message,
        ]);
        $service->setAdminDetailRead($id);
        return $response->redirect('/admin/customer_service/detail?id=' . $id);
    }

    #[RequestMapping(methods: ['GET'], path: 'detail')]
    public function detail(RequestInterface $request)
    {
        // 顯示幾筆
        $id = $request->input('id');
        $step = CustomerServiceDetail::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $models = CustomerServiceDetail::with(['user', 'member'])
            ->where('customer_service_id', $id)
            ->offset(($page - 1) * $step)->limit($step)->get();
        $total = CustomerServiceDetail::where('customer_service_id', $id)->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.customer_service_detail_control.message_record');
        $data['customer_service_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $models;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/customer_service/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($models, $step, $page);
        $data['paginator'] = $paginator->toArray();
        $data['customer_service_id'] = $id;
        return $this->render->render('admin.customerService.detail', $data);
    }
}
