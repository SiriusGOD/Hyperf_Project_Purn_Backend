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
use App\Model\Coin;
use App\Model\ImageGroup;
use App\Model\Member;
use App\Model\MemberLevel;
use App\Model\Order;
use App\Model\Product;
use App\Model\Video;
use App\Request\OrderRequest;
use App\Service\OrderService;
use App\Service\PayService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
class OrderController extends AbstractController
{
    /**
     * 獲取使用者訂單.
     */
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, OrderService $service)
    {
        $user_id = auth('jwt')->user()->getId();
        $order_status = $request->input('order_status');
        $offset = $request->input('page', 0);
        $limit = $request->input('limit', 0);
        $result = $service->searchUserOrder($user_id, $order_status, $offset, $limit);
        $data = [];
        $data['models'] = $result;
        $path = '/api/order/list?';
        $simplePaginator = new SimplePaginator($offset, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    /**
     * 新增使用者訂單.
     */
    #[RequestMapping(methods: ['POST'], path: 'create')]
    public function create(RequestInterface $request, OrderService $service, PayService $pay_service)
    {
        $data['user_id'] = auth('jwt')->user()->getId();
        $data['prod_id'] = $request->input('product_id', 0);
        if (empty($data['prod_id'])) {
            return $this->error('product id 字段是必须的', ErrorCode::BAD_REQUEST);
        }
        $data['payment_type'] = $request->input('payment_type', 0);
        $data['oauth_type'] = $request->input('oauth_type', 'web');
        $data['pay_proxy'] = $request->input('pay_proxy', 'online');
        $data['pay_method'] = $request->input('pay_method');

        // agent or online
        $base_service = di(\App\Service\BaseService::class);
        $data['ip'] = $base_service->getIp($request->getHeaders(), $request->getServerParams());

        // 撈取商品資料
        $data['product'] = Product::where('id', $data['prod_id'])->where('expire', 0)->first();
        if (empty($data['product'])) {
            return $this->error('查無商品資料', ErrorCode::BAD_REQUEST);
        }
        $data['product'] = $data['product']->toArray();
        $product = $data['product'];
        // 撈取會員資料
        $member = Member::where('id', $data['user_id'])->where('status', '<', Member::STATUS['DISABLE'])->first();
        $data['user'] = $member;
        if (empty($data['user'])) {
            return $this->error('查無會員資料', ErrorCode::BAD_REQUEST);
        }
        $data['user'] = $data['user']->toArray();
        $user = $data['user'];

        switch ($data['pay_method']) {
            case 'cash':
                // 現金
                // 確認商品是否為現金
                if ($product['currency'] == Product::CURRENCY[2]) {
                    return $this->error('該商品不能使用現金購買', ErrorCode::BAD_REQUEST);
                }
                // 生成支付鏈接(測試) -> 現金相關才需生成
                $pay_res = $pay_service->getPayUrl($data);
                if($pay_res['success'] != true)return $this->error('生成支付鏈接失敗', ErrorCode::BAD_REQUEST);
                $data['pay_order_id'] = isset($pay_res['data']['pay_order_id']) ? $pay_res['data']['pay_order_id'] : '';
                $data['pay_url'] = str_replace('&amp;', '&', $pay_res['data']['pay_url']);

                // 建立訂單
                $result = $service->createOrder($data);
                if ($result != false) {
                    $pay_res['data']['order_num'] = $result;
                    return $this->success($pay_res['data'], '訂單新增成功');
                }
                return $this->error('訂單新增失敗', ErrorCode::BAD_REQUEST);
                break;
            case 'coin':
                // 現金點數
                // 確認商品是否為現金點數
                if ($product['currency'] == Product::CURRENCY[2]) {
                    return $this->error('該商品不能使用現金點數購買', ErrorCode::BAD_REQUEST);
                }
                // 確認點數是否足夠
                if ($user['coins'] < $product['selling_price']) {
                    return $this->error('該會員現金點數不足', ErrorCode::BAD_REQUEST);
                }

                // 建立訂單
                $result = $service->createOrder($data);
                if ($result) {
                    // 扣現金點數
                    var_dump('扣現金點數');
                    $member->coins = $user['coins'] - $product['selling_price'];
                    // 如果是購買鑽石點數，則會員鑽石點數需要更新
                    if ($product['type'] == Product::TYPE_LIST[3]) {
                        $coin = Coin::where('id', $product['correspond_id'])->first();
                        $member->diamond_coins = (float) $member->diamond_coins + $coin->points + $coin->bonus;
                    }
                    // 如果是會員卡(則要做會員升等)
                    if ($product['type'] == Product::TYPE_LIST[2]) {
                        $data['order_number'] = $result;
                        $service->memberLevelUp($data);
                    }

                    $re = $member->save();
                    $pay_amount = $product['selling_price'];
                } else {
                    return $this->error('訂單新增失敗', ErrorCode::BAD_REQUEST);
                }

                break;
            case 'diamond_coin':
                // 鑽石點數
                // 確認商品是否可用鑽石點數購買
                if (empty($product['diamond_price'])) {
                    return $this->error('該商品不能使用鑽石點數購買', ErrorCode::BAD_REQUEST);
                }
                // 確認點數是否足夠
                if ($user['diamond_coins'] < $product['diamond_price']) {
                    return $this->error('該會員鑽石點數不足', ErrorCode::BAD_REQUEST);
                }

                // 建立訂單
                $result = $service->createOrder($data);
                if ($result) {
                    // 扣鑽石點數
                    var_dump('扣鑽石點數');
                    $member->diamond_coins = $user['diamond_coins'] - $product['diamond_price'];
                    $re = $member->save();
                    $pay_amount = $product['diamond_price'];
                }

                break;
            case 'diamond_quota':
                // 鑽石觀看次數
                // 確認會員等級
                if ($user['member_level_status'] != MemberLevel::TYPE_VALUE['diamond']) {
                    return $this->error('該會員不是鑽石會員', ErrorCode::BAD_REQUEST);
                }
                // 確認會員的鑽石觀看次數
                if ($user['diamond_quota'] <= 0) {
                    return $this->error('該會員沒有鑽石觀看次數', ErrorCode::BAD_REQUEST);
                }
                // 確認商品是否是影片或套圖
                if ($product['type'] != Product::TYPE_LIST[0] && $product['type'] != Product::TYPE_LIST[1]) {
                    return $this->error('該商品不可使用鑽石觀看次數購買', ErrorCode::BAD_REQUEST);
                }

                // 建立訂單
                $result = $service->createOrder($data);
                if ($result) {
                    // 扣次數
                    var_dump('扣鑽石觀看次數');
                    $member->diamond_quota = $user['diamond_quota'] - Product::QUOTA;
                    $re = $member->save();
                    $pay_amount = Product::QUOTA;

                    // 鑽石次數歸0時，判斷是否要降等!!!!
                    if ($user['diamond_quota'] - Product::QUOTA == 0) {
                        $service->memberLevelDown($data['user_id']);
                    }
                }

                break;
            case 'vip_quota':
                // VIP觀看次數
                // 確認會員等級
                if ($user['member_level_status'] != MemberLevel::TYPE_VALUE['vip']) {
                    return $this->error('該會員不是VIP會員', ErrorCode::BAD_REQUEST);
                }
                // 確認會員的VIP觀看次數
                if ($user['vip_quota'] <= 0) {
                    return $this->error('該會員沒有VIP觀看次數', ErrorCode::BAD_REQUEST);
                }
                // 確認商品是否是影片或套圖
                if ($product['type'] != Product::TYPE_LIST[0] && $product['type'] != Product::TYPE_LIST[1]) {
                    return $this->error('該商品不可使用VIP觀看次數購買', ErrorCode::BAD_REQUEST);
                }
                // 如果是圖片，確認圖片是否是vip或免費影片
                // if ($product['type'] == Product::TYPE_LIST[0]) {
                //     $img = ImageGroup::where('id', $product['correspond_id'])->first()->toArray();
                //     if ($img['pay_type'] == ImageGroup::IMAGE_GROUP_PAY_TYPE['diamond']) {
                //         return $this->error('該商品不可使用VIP觀看次數購買', ErrorCode::BAD_REQUEST);
                //     }
                // }

                // 如果是影片，確認影片是否是vip或免費影片
                if ($product['type'] == Product::TYPE_LIST[1]) {
                    $video = Video::where('id', $product['correspond_id'])->first()->toArray();
                    if ($video['is_free'] == Video::VIDEO_TYPE['diamond']) {
                        return $this->error('該商品不可使用VIP觀看次數購買', ErrorCode::BAD_REQUEST);
                    }
                }

                // 建立訂單
                $result = $service->createOrder($data);
                if ($result) {
                    // 扣次數
                    var_dump('扣VIP觀看次數');
                    $member->vip_quota = $user['vip_quota'] - Product::QUOTA;
                    $re = $member->save();
                    $pay_amount = Product::QUOTA;

                    // Vip次數歸0時，判斷是否要降等!!!!
                    if ($user['vip_quota'] - Product::QUOTA == 0) {
                        $service->memberLevelDown($data['user_id']);
                    }
                }

                break;
            case 'free_quota':
                // 免費觀看次數
                // 確認會員的免費觀看次數
                if ($user['free_quota'] <= 0) {
                    return $this->error('該會員沒有免費觀看次數', ErrorCode::BAD_REQUEST);
                }
                // 確認商品是否是影片或套圖
                if ($product['type'] != Product::TYPE_LIST[0] && $product['type'] != Product::TYPE_LIST[1]) {
                    return $this->error('該商品不可使用免費觀看次數購買', ErrorCode::BAD_REQUEST);
                }
                // 如果是圖片，確認圖片是否是免費圖片
                if ($product['type'] == Product::TYPE_LIST[0]) {
                    $img = ImageGroup::where('id', $product['correspond_id'])->first()->toArray();
                    if ($img['pay_type'] != ImageGroup::IMAGE_GROUP_PAY_TYPE['free']) {
                        return $this->error('該商品不可使用免費觀看次數購買', ErrorCode::BAD_REQUEST);
                    }
                }
                // 如果是影片，確認影片是否是免費影片
                if ($product['type'] == Product::TYPE_LIST[1]) {
                    $video = Video::where('id', $product['correspond_id'])->first()->toArray();
                    if ($video['is_free'] != Video::VIDEO_TYPE['free']) {
                        return $this->error('該商品不可使用免費觀看次數購買', ErrorCode::BAD_REQUEST);
                    }
                }
                
                // 建立訂單
                $result = $service->createOrder($data);
                if ($result) {
                    // 扣次數
                    var_dump('扣免費觀看次數');
                    $member->free_quota = $user['free_quota'] - Product::QUOTA;
                    $re = $member->save();
                    $pay_amount = Product::QUOTA;
                }

                break;
        }

        // 變更訂單狀態為已完成 (除了現金購買外)
        if ($re) {
            $pay_res['data']['order_num'] = $result;
            $order = Order::where('order_number', $result)->first();
            $order->pay_amount = $pay_amount;
            $order->status = Order::ORDER_STATUS['finish'];
            $order->save();
            return $this->success($pay_res['data'], '購買商品成功');
        }
        $this->error('購買商品失敗', ErrorCode::BAD_REQUEST);

        // 生成支付鏈接(測試) -> 現金相關才需生成
        // $pay_res = $pay_service->getPayUrl($user_id, $prod_id, $payment_type, $oauth_type, $pay_proxy, $ip);
        // if (isset($pay_res['success']) && $pay_res['success'] == true) {
        //     // 建立訂單
        //     $pay_order_id = isset($pay_res['data']['pay_order_id']) ? $pay_res['data']['pay_order_id'] : '';
        //     $result = $service->createOrder($user_id, $prod_id, $payment_type, str_replace('&amp;', '&', $pay_res['data']['pay_url']), $pay_proxy, $pay_order_id);
        //     $pay_res['data']['order_num'] = $result;
        //     if ($result != false) {
        //         return $this->success($pay_res['data'], '訂單新增成功');
        //     }
        //     return $this->error('訂單新增失敗', ErrorCode::BAD_REQUEST);
        // }
        // return $this->error('生成支付鏈接失敗', ErrorCode::BAD_REQUEST);
    }

    /**
     * 修改訂單狀態.
     */
    #[RequestMapping(methods: ['POST'], path: 'delete')]
    public function delete(RequestInterface $request, OrderService $service)
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

    /**
     * 查詢訂單資訊.
     */
    #[RequestMapping(methods: ['POST'], path: 'search')]
    public function search(RequestInterface $request, OrderService $service)
    {
        $order_num = $request->input('order_num');
        $result = $service->searchOrders($order_num, '');
        return $this->success(['models' => $result->toArray()]);
    }
}
