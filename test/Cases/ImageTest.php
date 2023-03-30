<?php

namespace HyperfTest\Cases;

use App\Model\Image;
use App\Model\Member;
use App\Model\User;
use App\Service\UserService;
use HyperfTest\HttpTestCase;

class ImageTest extends HttpTestCase
{
    public function testList()
    {
        $data = $this->client->get('/api/image/list');

        $this->assertSame(200, (int) $data['code']);
    }

    public function testSearch()
    {
        $data = $this->client->get('/api/image/search', [
            'keyword' => 'test',
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testSuggest()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(UserService::class)->saveToken($user->id, $token);
        $data = $this->client->get('/api/image/suggest', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame(200, (int) $data['code']);
    }

    public function testLike()
    {
        $user = Member::first();

        $model = new Image();
        $model->user_id = $user->id;
        $model->title = str_random();
        $model->thumbnail = str_random();
        $model->url = str_random();
        $model->description = str_random();
        $model->like = 0;
        $model->group_id = 0;
        $model->save();

        $token = auth()->login($user);
        make(UserService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/image/like', [
            'id' => $model->id,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $this->assertSame($model->id, (int) $data['data']['id']);

        $model->forceDelete();
    }
}