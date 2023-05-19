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
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/tag/list', [], [ 'Authorization' => 'Bearer ' . $token]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testGroupListTest()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/tag/groupList', [
            'id' => 1,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testSearchGroupTags()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/tag/searchGroupTags', [
            'group_id' => 1,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testGetTagDetail()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/tag/getTagDetail', [
            'tag_id' => 1,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testSearch()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/tag/search', [
            'tag_id' => 1,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }
}