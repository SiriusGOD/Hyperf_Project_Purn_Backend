<?php

namespace HyperfTest\Cases;

use App\Model\User;
use App\Service\UserService;
use App\Task\ProductTask;
use HyperfTest\HttpTestCase;
use PHPUnit\Util\Json;

class OrderTest extends HttpTestCase
{
    public function testList()
    {
        $user = User::first();
        $token = auth()->login($user);
        make(UserService::class)->saveToken($user->id, $token);
        $data = $this->client->get('/api/order/list', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int)$data['code']);
    }

    public function testCreate()
    {
        $user = User::first();
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
}