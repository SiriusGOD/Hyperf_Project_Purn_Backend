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
namespace App\Controller\Api;

use App\Constants\ErrorCode;
use App\Controller\AbstractController;
use App\Model\Order;
use App\Request\OrderRequest;
use App\Service\OrderService;
use App\Service\PayService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * @Controller
 */
class OrderController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="get")
     * 獲取使用者訂單
     */
    public function list(OrderRequest $request, OrderService $service)
    {
<<<<<<< HEAD
        $user_id = auth()->user()->getId();
=======
        $user_id = auth('jwt')->user()->getId();
>>>>>>> tw0691_0327_product_api
        $order_status = $request->input('order_status');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);
        $result = $service->searchUserOrder($user_id, $order_status, $offset, $limit);
        return $this->success($result);
    }

    /**
     * @RequestMapping(path="create", methods="post")
     * 新增使用者訂單
     */
    public function create(OrderRequest $request, OrderService $service, PayService $pay_service)
    {
<<<<<<< HEAD
        $user_id = auth()->user()->getId();
=======
        $user_id = auth('jwt')->user()->getId();
>>>>>>> tw0691_0327_product_api
        $prod_id = $request->input('product_id', 0);
        if (empty($prod_id)) {
            return $this->error('product id 字段是必须的', ErrorCode::BAD_REQUEST);
        }
        $payment_type = $request->input('payment_type', 1);
        $oauth_type = $request->input('oauth_type', 'web');
        $pay_proxy = $request->input('pay_proxy', 'online'); // agent or online

        $base_service = di(\App\Service\BaseService::class);
        $ip = $base_service->getIp($request->getHeaders(), $request->getServerParams());

        // 生成支付鏈接(測試)
        $pay_res = $pay_service->getPayUrl($user_id, $prod_id, $payment_type, $oauth_type, $pay_proxy, $ip);
        if (isset($pay_res['success']) && $pay_res['success'] == true) {
            // 建立訂單
            $result = $service->createOrder($user_id, $prod_id, $payment_type, str_replace('&amp;', '&', $pay_res['data']['pay_url']), $pay_proxy);
            $pay_res['data']['order_num'] = $result;
            if ($result != false) {
                return $this->success($pay_res['data'], '訂單新增成功');
            }
            return $this->error('訂單新增失敗', ErrorCode::BAD_REQUEST);
        }
        return $this->error('生成支付鏈接失敗', ErrorCode::BAD_REQUEST);
    }

    /**
     * @RequestMapping(path="delete", methods="post")
     * 修改訂單狀態
     */
    public function delete(OrderRequest $request, OrderService $service)
    {
        $user_id = auth('jwt')->user()->getId();
        $order_num = $request->input('order_num');
        $order_status = Order::ORDER_STATUS['delete'];
        $result = $service->delete($user_id, $order_num, $order_status);
        if ($result) {
            return $this->success([], '訂單狀態更新成功');
        }
        return $this->error('該會員下查無此訂單或該訂單已取消', ErrorCode::BAD_REQUEST);
    }
}
