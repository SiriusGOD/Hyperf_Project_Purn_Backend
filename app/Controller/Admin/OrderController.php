<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Middleware\PermissionMiddleware;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Request\OrderRequest;
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
use HyperfExt\Jwt\Contracts\JwtFactoryInterface;
use HyperfExt\Jwt\Contracts\ManagerInterface;
use HyperfExt\Jwt\Jwt;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Hyperf\DbConnection\Db;


/**
 * @Controller
 * @Middleware(PermissionMiddleware::class)
 */
class OrderController extends AbstractController
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
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        // 顯示幾筆
        $step = Order::PAGE_PER;
        $page = $request->input('page') ? intval($request->input('page'), 10) : 1;
        $query = Order::join('users','orders.user_id','users.id')
            ->select('orders.*','users.name')
            ->offset(($page - 1) * $step)
            ->limit($step);
        $advertisements = $query->get();

        $query = Order::select('*');
        $total = $query->count();

        $data['last_page'] = ceil($total / $step);
        if ($total == 0) {
            $data['last_page'] = 1;
        }
        $data['navbar'] = trans('default.order_control.order_control');
        $data['order_active'] = 'active';
        $data['total'] = $total;
        $data['datas'] = $advertisements;
        $data['page'] = $page;
        $data['step'] = $step;
        $path = '/admin/order/index';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        $paginator = new Paginator($advertisements, $step, $page);

        $data['paginator'] = $paginator->toArray();

        return $this->render->render('admin.order.index', $data);
    }

    /**
     * @RequestMapping(path="edit", methods={"get"})
     */
    public function edit(RequestInterface $request)
    {
        $id = $request->input('id');
        $data['model'] = Order::join('users','orders.user_id','users.id')
            ->select('orders.*','users.name')
            ->findOrFail($id);
        $data['model_details'] = Order::join('order_details','orders.id','order_details.order_id')
            ->select('order_details.*')
            ->where('orders.id',$id)
            ->get();
        $data['navbar'] = trans('default.order_control.order_edit');
        $data['order_active'] = 'active';
        return $this->render->render('admin.order.form', $data);
    }

    /**
     * @RequestMapping(path="changeStatus", methods={"POST"})
     */
    public function changeStatus(RequestInterface $request, ResponseInterface $response, OrderService $service): PsrResponseInterface
    {
        $query = Order::where('id', $request->input('id'));
        $record = $query->first();

        if (empty($record)) {
            return $response->redirect('/admin/order/index');
        }

        $record->status = $request->input('order_status', 1);
        $record->save();
        $service->updateCache();
        return $response->redirect('/admin/order/index');
    }

    /**
     * 產生當天訂單流水號
     *
     * @return    string
     */
    function getSn()
    {
        $sql = 'SELECT o.order_number '
            . 'FROM orders AS o '
            . 'ORDER BY o.order_number DESC LIMIT 1';
        $res = Db::select($sql);

        var_dump($res[0]->order_number);

        if(!isset($res[0]->order_number)){
            $lastNum = 0;
        }else{
            var_dump(mb_substr($res[0]->order_number, -5, 5));
            $lastNum = (int)mb_substr($res[0]->order_number, -5, 5);
        }

        $orderSn = 'PO' . date('Ymd', $_SERVER['REQUEST_TIME']) . str_pad((string)($lastNum + 1), 5, '0', STR_PAD_LEFT);

        return $orderSn;
    }
}
