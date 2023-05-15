<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Cases;
use App\Service\MemberService;
use App\Model\Member;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use Hyperf\Utils\Str;

/**
 * @internal
 * @coversNothing
 */
class ProxyApiTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $memberService;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->memberService = make(MemberService::class);
    }
  
    //分享碼
    public function testAddmember()
    {
        $model = new \App\Model\Member();
        $model->name = 'test'.time();
        $model->password = password_hash('q123456', PASSWORD_DEFAULT);
        $model->sex = 1;
        $model->age = 20;
        $model->avatar = '';
        $model->email = 'admin'.time().'@admin.com';
        $model->phone = '0912'.rand(111111,999999);
        $model->status = 1;
        $model->member_level_status =0;
        $model->aff= Str::random(5);
        $model->save();
        $this->assertNotNull( $model->id);
    }

    //我的收益
    public function testMyIncome()
    {
        $member = Member::where('id' ,36)->first();
        $token = auth()->login($member);
        make(MemberService::class)->saveToken($member->id, $token);
        $data = $this->client->post('/api/proxy/myIncome', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r($data);
        $this->assertSame(200, (int)$data['code']);
    }

    //我的收益
    public function testDownline()
    {
        $member = Member::where('id' ,50)->first();
        $token = auth()->login($member);
        make(MemberService::class)->saveToken($member->id, $token);
        $data = $this->client->post('/api/proxy/downline', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        //print_r($data);
        $this->assertSame(200, (int)$data['code']);
    }
}
