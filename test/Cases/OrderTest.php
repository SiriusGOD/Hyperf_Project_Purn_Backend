<?php

namespace HyperfTest\Cases;

use App\Model\Member;
use App\Service\MemberService;
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
        $user = Member::find(24);
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $postData = [
            'product_id'   => 9,
            'payment_type' => 1,
            'pay_method'   =>'cash' ,
            'oauth_type'   => 'web',
            'pay_proxy'    => 'online',
        ];

        print_r($postData);

        $data = $this->client->post('/api/order/create', $postData ,[
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r(['qqqq' ,$data]);
        $this->assertSame(200, (int)$data['code']);
    }

}
