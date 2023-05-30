<?php

namespace HyperfTest\Cases;

use App\Model\CustomerService;
use App\Model\CustomerServiceDetail;
use App\Model\ImageGroup;
use App\Model\Member;
use App\Model\MemberCategorization;
use App\Model\MemberCategorizationDetail;
use App\Model\User;
use App\Model\Video;
use App\Service\MemberService;
use App\Service\UserService;
use HyperfTest\HttpTestCase;
use PHPUnit\Util\Json;

class MemberCategoryTest extends HttpTestCase
{
    public function testList()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/member_categorization/list?is_main=1', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testIsExist()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $model = new MemberCategorization;
        $model->member_id = $user->id;
        $model->name = str_random();
        $model->hot_order = 0;
        $model->is_default = 0;
        $model->is_first = 0;
        $model->save();

        $row = new MemberCategorizationDetail();
        $row->member_categorization_id = $model->id;
        $row->type = ImageGroup::class;
        $row->type_id = 1;
        $row->total_click = 0;
        $row->save();

        $data = $this->client->post('/api/member_categorization/detail/exist?type=image_group&type_id=1', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $row->forceDelete();
        $model->forceDelete();

        $this->assertSame(1, (int) $data['data']['is_exist']);
    }

    public function testDetail()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $data = $this->client->post('/api/member_categorization/detail/list?member_categorization_id=0&sort_by=2&is_asc=2', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testDetailCount()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $model = new MemberCategorization;
        $model->member_id = $user->id;
        $model->name = str_random();
        $model->hot_order = 0;
        $model->is_default = 0;
        $model->is_first = 0;
        $model->save();

        $row = new MemberCategorizationDetail();
        $row->member_categorization_id = $model->id;
        $row->type = ImageGroup::class;
        $row->type_id = 1;
        $row->total_click = 0;
        $row->save();

        $data = $this->client->post('/api/member_categorization/detail/count?id=0', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $row->forceDelete();
        $model->forceDelete();

        $this->assertSame(1, (int) $data['data']['image_group_count']);
    }

    public function testCreate()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $data = $this->client->post('/api/member_categorization/create', [
            'name' => str_random(),
            'hot_order' => 0,
            'is_default' => 1,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testUpdate()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $model = new MemberCategorization;
        $model->member_id = $user->id;
        $model->name = str_random();
        $model->hot_order = 0;
        $model->is_default = 0;
        $model->is_first = 0;
        $model->save();

        $data = $this->client->post('/api/member_categorization/update', [
            'name' => str_random(),
            'id' => $model->id + 3,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $model->forceDelete();

        $this->assertSame(200, (int) $data['code']);
    }

    public function testUpdateOrder()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $model1 = new MemberCategorization;
        $model1->member_id = $user->id;
        $model1->name = str_random();
        $model1->hot_order = 1;
        $model1->is_default = 0;
        $model1->is_first = 0;
        $model1->save();
        $ids = [];
        $ids[] = $model1->id;

        $model = new MemberCategorization;
        $model->member_id = $user->id;
        $model->name = str_random();
        $model->hot_order = 2;
        $model->is_default = 0;
        $model->is_first = 0;
        $model->save();
        $ids = [];
        $ids[] = $model->id;

        $data = $this->client->post('/api/member_categorization/update/order', [
            'ids' => $ids,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $model->forceDelete();
        $model1->forceDelete();

        $this->assertSame(200, (int) $data['code']);
    }
}