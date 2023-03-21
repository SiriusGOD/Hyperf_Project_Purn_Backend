<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use App\Service\OrderService;
use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use App\Request\OrderRequest;
use App\Constants\ApiCode;
use App\Constants\ErrorCode;

/**
 * @Controller
 */
class OrderController extends AbstractController
{
    /**
     * @RequestMapping(path="getUserOrder", methods="post")
     * 獲取使用者訂單
     */
    public function getUserOrder(OrderRequest $request, OrderService $service)
    {
        $user_id = $request->input('user_id',0);
        $order_status = $request->input('order_status');
        $offset = $request->input('offset',0);
        $limit = $request->input('limit',0);
        $result = $service->searchUserOrder($user_id, $order_status, $offset ,$limit);
        return $this->success($result);
    }

    /**
     * @RequestMapping(path="create", methods="post")
     * 新增使用者訂單
     */
    public function create(OrderRequest $request, OrderService $service)
    {
        $user_id = $request->input('user_id',0);
        $prod_id = $request->input('product_id', 0);
        if(empty($prod_id))return $this->error('product id 字段是必须的', ErrorCode::BAD_REQUEST);
        $payment_type = $request->input('product_id', 1);
        $result = $service->createOrder($user_id, $prod_id, $payment_type);
        if($result)return $this->success([], '訂單新增成功');
        return $this->error('訂單新增失敗', ErrorCode::BAD_REQUEST );
    }

    /**
     * @RequestMapping(path="update", methods="post")
     * 修改訂單狀態
     */
    public function update(OrderRequest $request, OrderService $service)
    {
        $user_id = $request->input('user_id',0);
        $order_num = $request->input('order_num');
        $order_status = $request->input('order_status');
        $result = $service -> updateOrderStatus($user_id, $order_num, $order_status);
        if($result)return $this->success([], '訂單狀態更新成功');
        return $this->error('該會員下查無此訂單或該訂單已取消', ErrorCode::BAD_REQUEST );
    }
}
