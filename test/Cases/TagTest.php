<?php

namespace HyperfTest\Cases;

use App\Model\Member;
use App\Model\User;
use App\Service\MemberService;
use App\Service\UserService;
use HyperfTest\HttpTestCase;
use PHPUnit\Util\Json;

class TagTest extends HttpTestCase
{
    public function testList()
    {
        $data = $this->client->post('/api/tag/list');

        $this->assertSame(200, (int) $data['code']);
    }

    public function testCreate()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/tag/create', [
            'name' => str_random(),
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }
}