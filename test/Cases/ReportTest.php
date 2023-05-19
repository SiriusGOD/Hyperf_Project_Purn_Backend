<?php

namespace HyperfTest\Cases;

use App\Model\Member;
use App\Model\Report;
use App\Model\User;
use App\Service\MemberService;
use App\Service\UserService;
use HyperfTest\HttpTestCase;
use PHPUnit\Util\Json;

class ReportTest extends HttpTestCase
{
    public function testList()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/report/list', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testCreate()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/report/create', [
            'type' => 2,
            'model_type' => 'image_group',
            'model_id' => 3,
            'content' => 'test',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testCancel()
    {
        $user = Member::first();
        $token = auth()->login($user);
        $report = new Report();
        $report->member_id = $user->id;
        $report->model_type = 'image_group';
        $report->model_id = 3;
        $report->type = 2;
        $report->status = Report::STATUS['new'];
        $report->save();
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/report/cancel', [
            'model_type' => 'image_group',
            'model_id' => 3,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }
}