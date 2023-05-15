<?php

namespace HyperfTest\Cases;

use App\Model\Member;
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
        $user = Member::find(37);
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/order/create', [
            'product_id' => 28,
            'payment_type' => 0,
            'pay_method'=>'coin' ,
            'oauth_type' => 'web',
            'pay_proxy' => 'online',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r(['qqqq' ,$data]);
        $this->assertSame(200, (int)$data['code']);
    }

}
