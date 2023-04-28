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
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $str = '{"version": "1.0.3", "bundle_id": "tips.ks", "channel": "", "via": "", "build_id": "", "oauth_type": "android", "oauth_id": "dd6537bf016a6c09741a95b5590275ca", "account_name": "中国银行","account": "6217003370004076152","name": "玉莲","withdraw_amount": 500.00}';
        $json = json_decode($str,true);
        $data = $this->client->post('/api/member_cash/withdraw',$json, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r([$data]);
        $this->assertSame(200, (int)$data['code']);
    }

}
