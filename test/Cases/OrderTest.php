<?php

namespace HyperfTest\Cases;

use App\Model\Member;
use App\Model\Product;
use App\Model\Order;
use App\Model\User;
use App\Service\MemberService;
use App\Service\UserService;
use HyperfTest\HttpTestCase;

class OrderTest extends HttpTestCase
{
    public function testList()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->get('/api/order/list', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int)$data['code']);
    }
    //買影片-測試代理返傭
    public function testCreateCoinBuy()
    {
        $user = Member::where("coins",'>',100)->orderBy('id','desc')->first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $p  = Product::where("type",'video')->where("selling_price", ">" ,50)->first();
        $postData = [
            'product_id' => $p->id,
            'payment_type' => 0,
            'pay_method'=>'coin' ,
            'oauth_type' => 'web',
            'pay_proxy' => 'online',
        ];
        $data = $this->client->post('/api/order/create', $postData, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r(['qqqq' ,$data]);
        $this->assertSame(200, (int)$data['code']);
    }

}
