<?php

namespace HyperfTest\Cases;

use App\Model\Member;
use App\Model\Order;
use App\Model\User;
use App\Service\UserService;
use HyperfTest\HttpTestCase;

class OrderTest extends HttpTestCase
{
    public function testList()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(UserService::class)->saveToken($user->id, $token);
        $data = $this->client->get('/api/order/list', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int)$data['code']);
    }

    public function testCreate()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(UserService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/order/create', [
            'product_id' => 1,
            'payment_type' => 1,
            'oauth_type' => 'web',
            'pay_proxy' => 'online',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int)$data['code']);
    }

    public function testDelete()
    {
        $orderNum = str_random(99);
        $model = new Order();
        $model->user_id = 1;
        $model->order_number = $orderNum;
        $model->address = 'test';
        $model->email = 'test';
        $model->mobile = 'test';
        $model->telephone = 'test';
        $model->payment_type = 1;
        $model->currency = 'test';
        $model->total_price = 100;
        $model->pay_way = 'test';
        $model->pay_url = 'test';
        $model->pay_proxy = 'test';
        $model->status = Order::ORDER_STATUS['create'];
        $model->save();
        $user = User::first();
        $token = auth()->login($user);
        make(UserService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/order/delete', [
            'order_num' => $orderNum,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $model->forceDelete();
        $this->assertSame(200, (int)$data['code']);
    }
}