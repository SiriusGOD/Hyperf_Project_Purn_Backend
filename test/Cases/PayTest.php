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

/**
 * @internal
 * @coversNothing
 */
class PayTest extends HttpTestCase
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
        $member = Member::where('id' ,24)->first();
        $token = auth()->login($member);

        $j = '{"order_id":"test_order_id_xgn4mwrd","third_id":"test_order_id_xgn4mwrd","pay_money":"2000","pay_time" : "1556972314","success":"200","sign":"9030e149a0b49cd0e7a6a0436aa303f9"}';
        $jcode = json_decode($j,true);
        make(MemberService::class)->saveToken($member->id, $token);

        print_r($jcode);

        $data = $this->client->post('/api/pay/notifyPayAction', $jcode, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r($data);
        $this->assertSame(200, (int)$data['code']);
    }

}
