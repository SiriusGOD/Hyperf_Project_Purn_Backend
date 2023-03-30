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
use App\Model\Order;
use App\Service\OrderService;
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
class OrderController extends AbstractController
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
        $step = Order::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Order::join('users', 'orders.user_id', 'users.id')->select('orders.*', 'users.name')->offset(($page - 1) * $step)->limit($step);
        $orders = $query->get();
        $query = Order::select('*');
        $total = $query->count();
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.order_control.order_control');
        $data['order_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $orders;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/order/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($orders, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.order.index', $data);
    }

    #[RequestMapping(methods: ['GET'], path: 'edit')]
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Order::join('users', 'orders.user_id', 'users.id')->select('orders.*', 'users.name')->findOrFail($id);
        $data['model_details'] = Order::join('order_details', 'orders.id', 'order_details.order_id')->select('order_details.*')->where('orders.id', $id)->get();
        $data['navbar'] = trans('default.order_control.order_edit');
        $data['order_active'] = 'active';
        return $this->render->render('admin.order.form', $data);
    }

    #[RequestMapping(methods: ['POST'], path: 'changeStatus')]
    public function changeStatus(RequestInterface $request, ResponseInterface $response, OrderService $service): PsrResponseInterface
    {
        $query = Order::where('id', $request->input('id'));
        $record = $query->first();
        if (empty($record)) {
            return $response->redirect('/admin/order/index');
        }
        $record->status = $request->input('order_status', 1);
        $record->save();
        // $service->updateCache();
        return $response->redirect('/admin/order/index');
    }

    #[RequestMapping(methods: ['GET'], path: 'search')]
    public function search(RequestInterface $request, ResponseInterface $response, OrderService $service)
    {
        // 顯示幾筆
        $step = Order::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $order_number = $request->input('order_number');
        $order_status = $request->input('order_status');
        $orders = $service->searchOrders($order_number, $order_status, $page);
        $total = $service->getOrdersCount($order_number, $order_status);
        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.order_control.order_control');
        $data['order_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $orders;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/order/search';
        if (! empty($order_status)) {
            $data['next'] = $path . '?page=' . ($page + 1) . '&order_status=' . $order_status;
            $data['prev'] = $path . '?page=' . ($page - 1) . '&order_status=' . $order_status;
        } else {
            $data['next'] = $path . '?page=' . ($page + 1);
            $data['prev'] = $path . '?page=' . ($page - 1);
        }
        $paginator = new Paginator($orders, $step, $page);
        $data['paginator'] = $paginator->toArray();
        return $this->render->render('admin.order.index', $data);
    }
}
