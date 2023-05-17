<?php

namespace HyperfTest\Cases;

use App\Model\CustomerService;
use App\Model\CustomerServiceDetail;
use App\Model\Member;
use App\Model\User;
use App\Service\MemberService;
use App\Service\UserService;
use HyperfTest\HttpTestCase;
use PHPUnit\Util\Json;

class CustomerServiceTest extends HttpTestCase
{
    public function testTypeList()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/customer_service/type/list', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testList()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $model = new CustomerService();
        $model->member_id = $user->id;
        $model->type = 1;
        $model->title = 'test';
        $model->save();

        $id = $model->id;

        $data = $this->client->post('/api/customer_service/list', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $model->forceDelete();

        $this->assertSame($id, (int) $data['data']['models'][0]['id']);
    }

    public function testDetail()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $model = new CustomerService();
        $model->member_id = $user->id;
        $model->type = 1;
        $model->title = 'test';
        $model->save();

        $detail = new CustomerServiceDetail();
        $detail->customer_service_id = $model->id;
        $detail->member_id = $user->id;
        $detail->message = 'test';
        $detail->is_read = 0;
        $detail->save();

        $detailId = $detail->id;

        $data = $this->client->post('/api/customer_service/detail', [
            'id' => $model->id
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $model->forceDelete();
        $detail->forceDelete();

        $this->assertSame($detailId, (int) $data['data']['models'][0]['id']);
    }

    public function testCreate()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/customer_service/create', [
            'type' => 1,
            'title' => 'test',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testReply()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $model = new CustomerService();
        $model->member_id = $user->id;
        $model->type = 1;
        $model->title = 'test';
        $model->save();

        $data = $this->client->post('/api/customer_service/reply', [
            'id' => $model->id,
            'message' => 'test',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }
}