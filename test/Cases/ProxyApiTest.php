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

    //我的收益
    public function testMyIncome()
    {
        $member = Member::where('id' ,83)->first();
        $token = auth()->login($member);
        make(MemberService::class)->saveToken($member->id, $token);
        $data = $this->client->post('/api/proxy/myIncome', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertSame(200, (int)$data['code']);
    }

    //下線
    public function testtWallet()
    {
        $member = Member::where('id' ,1)->first();
        $token = auth()->login($member);
        make(MemberService::class)->saveToken($member->id, $token);
        $data = $this->client->post('/api/proxy/wallet', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r($data);
        $this->assertSame(200, (int)$data['code']);
    }
    //下線
    public function tesDownline()
    {
        $member = Member::where('id' ,74)->first();
        $token = auth()->login($member);
        make(MemberService::class)->saveToken($member->id, $token);
        $data = $this->client->post('/api/proxy/downline', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r($data);
        $this->assertSame(200, (int)$data['code']);
    }
}
