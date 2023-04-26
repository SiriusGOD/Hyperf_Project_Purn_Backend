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
namespace HyperfTest\Cases;
use App\Service\ProxyService;
use App\Service\MemberService;
use App\Model\Order;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ProxyOrderTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $memberService;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->memberService = make(MemberService::class);
    }
  
    public function testGenOrder()
    {
        $member = $this->memberService->getProxy();
        $token = auth()->login($member);
        make(MemberService::class)->saveToken($member->id, $token);
        $string = '{"product_id":6,"payment_type":1,"oauth_type":"android","pay_proxy":"online","pay_method":"cash"}';
        $json = json_decode($string,true);
        $data = $this->client->post('/api/order/create', $json ,
          [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertSame("訂單新增成功", $data['msg']);
    }

    public function testPayOrder()
    {
        $member = $this->memberService->getProxy();
        $token = auth()->login($member);
        $order = Order::orderBy("id","desc")->first();
        make(MemberService::class)->saveToken($member->id, $token);
        $string = '{"order_id":"'.$order->pay_order_id.'","third_id":"test_order_id_xgkxn3is","pay_money":"'.$order->total_price.'","pay_time":"'.time().'","success":"200","sign":""}';
        $json = json_decode($string, true);
        print_r($json);
        $data = $this->client->post('/api/pay/notifyPayAction', $json ,
          [
             'Authorization' => 'Bearer ' . $token,
          ]
        );
        print_r($data);
        $this->assertSame(200, (int)$data['code']);
    }

}
