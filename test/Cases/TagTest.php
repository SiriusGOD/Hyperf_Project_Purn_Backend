<?php

namespace HyperfTest\Cases;

use App\Model\User;
use App\Service\UserService;
use HyperfTest\HttpTestCase;
use PHPUnit\Util\Json;

class TagTest extends HttpTestCase
{
    public function testList()
    {
        $data = $this->client->get('/api/tag/list');

        $this->assertSame(200, (int) $data['code']);
    }

    public function testCreate()
    {
        $user = User::first();
        $token = auth()->login($user);
        make(UserService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/tag/create', [
            'name' => str_random(),
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }
}