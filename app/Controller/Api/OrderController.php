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
use App\Service\PayService;

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
    public function create(OrderRequest $request, OrderService $service, PayService $pay_service)
    {
        $user_id = $request->input('user_id',0);
        $prod_id = $request->input('product_id', 0);
        if(empty($prod_id))return $this->error('product id 字段是必须的', ErrorCode::BAD_REQUEST);
        $payment_type = $request->input('product_id', 1);
        $oauth_type = $request->input('oauth_type', 'web');
        $pay_proxy = $request->input('pay_proxy', 'online'); // agent or online

        $base_service = di(\App\Service\BaseService::class);
        $ip = $base_service->getIp($request->getHeaders(), $request->getServerParams());

        // 生成支付鏈接(測試)
        $pay_res = $pay_service -> getPayUrl($user_id, $prod_id, $payment_type, $oauth_type, $pay_proxy, $ip);
        if(isset($pay_res['success']) && $pay_res['success'] == true){
            // 建立訂單
            $result = $service->createOrder($user_id, $prod_id, $payment_type, str_replace('&amp;', '&', $pay_res['data']['pay_url']), $pay_proxy);
            $pay_res['data']['order_num'] = $result;
            if($result != false)return $this->success($pay_res['data'], '訂單新增成功');
            return $this->error('訂單新增失敗', ErrorCode::BAD_REQUEST );
        }
        return $this->error('生成支付鏈接失敗', ErrorCode::BAD_REQUEST );
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
