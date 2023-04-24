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
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;

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
  
    //註冊2級代理
    public function testMemberShare()
    {
        $member = $this->memberService->getProxy();
        $token = auth()->login($member);
        make(MemberService::class)->saveToken($member->id, $token);
        $data = $this->client->get('/api/proxy/share', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r($data);
        $this->assertSame(200, (int)$data['code']);
    }

}
