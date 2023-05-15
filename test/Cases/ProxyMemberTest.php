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
use App\Service\ProxyService;
use App\Model\Member;
use App\Service\MemberService;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ProxyMemberTest extends HttpTestCase
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
    //註冊一級代理
    //public function testResisterProxyLevel1()
    //{
    //    $insertArray = self::memberExp();
    //    $q = "head".rand(1222,33333);
    //    $insertArray["name"] = $q;
    //    $insertArray["email"] =$q."@example.com";
    //    $insertArray["device_id"] = md5( (string)time().'9d9d73186f062f2078eebf29e93d955c');
    //    $data = $this->client->post('/api/member/login', $insertArray);
    //    $this->assertSame(200, (int)$data['code']);
    //}
  
    //註冊2級代理
    public function testResisterProxySecond()
    {
        $member = $this->memberService->getProxy();
        $insertArray = self::memberExp();
        $q = "s_".date("YmdHis")."_".rand(11,99);
        $insertArray["name"] = $q;
        //$insertArray["account"] = $q;
        $insertArray["email"] =$q."@example.com";
        $insertArray["invited_code"] = $member->aff;
        $insertArray["device_id"] = md5((string)$member->aff.time().date('YmdHis') );
        $data = $this->client->post('/api/member/login', $insertArray);
        $this->assertSame(200, (int)$data['code']);
    }

    public function memberExp(){
        return  [
            'name' => 'John',
            'password' => 'a123456',
            'email' => 'john@example.com',
            'member_level_status' => 1,
            'device' => 'ios',
            'invited_by' => 0,
            'invited_num' => 0,
            'tui_coins' => 0.00,
            'total_tui_coins' => 0.00,
        ];
    }

    //更新會員
    public function testUpdateMember()
    {
        $user = Member::orderBy('id','desc')->first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $insertArray = self::memberExp();
        $q = "a".rand(100010,999999);
        $insertArray["account"] = $q;
        $insertArray["email"] = $q."@example.com";
        $insertArray["password"] ='a123456';
        $data = $this->client->post('/api/member/update', $insertArray ,[
            'Authorization' => 'Bearer ' . $token,
        ]
        );
        $this->assertSame(200, (int)$data['code']);
    }
    //更新會員
    public function testLoginMember()
    {
        $insertArray["account"] = "a974866";
        $insertArray["device"] = "web";
        $insertArray["device_id"] = rand(111111111,9999999999)."web";
        $insertArray["password"] ='a123456';
        $data = $this->client->post('/api/member/login', $insertArray );
        print_r($data);
        $this->assertSame(200, (int)$data['code']);
    }
}
