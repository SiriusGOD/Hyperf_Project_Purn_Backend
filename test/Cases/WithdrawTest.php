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

use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Model\Member;
use App\Service\MemberService;
use App\Service\WithdrawService;

/**
 * @internal
 * @coversNothing
 */
class WithdrawTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
  
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
    }
    //測試提現 
    public function testWithdraw()
    {
        $user = Member::find(87);
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $str = '{"name":"玉莲","account":"6217003370004076152","bank_type":1,"withdraw_amount": 500.00,"password":"a123456"}';
        $json = json_decode($str,true);
        $data = $this->client->post('/api/member_cash/withdraw',$json, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r([$data ,'????']);
        $this->assertSame(200, (int)$data['code']);
    }

    //測試提現 
    public function testWithdrawList()
    {
        $user = Member::first();
        $token = auth()->login($user);
        $limit =2;
        make(MemberService::class)->saveToken($user->id, $token);
        $str = '{"page":0,"limit":'.$limit.'}';
        $json = json_decode($str,true);
        $data = $this->client->post('/api/member_cash/withdrawList',$json, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertSame( $limit, count($data['data']['models']) );
    }
}
