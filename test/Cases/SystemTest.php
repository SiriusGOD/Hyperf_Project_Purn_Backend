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

use App\Model\Member;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\MemberService;
use App\Util\URand;

/**
 * @internal
 * @coversNothing
 */
class SystemTest extends HttpTestCase
{
    //測試withdraw type 
    public function testWtihdrawType()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $data = $this->client->post('/api/system/withdraw_type', [
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r([$data, 'ss' ]);

    }
    //測試withdraw rate 
    public function teSuggestByUser()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        $data = $this->client->post('/api/system/withdraw_rate', [
            'name' => str_random(),
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);


        $this->assertSame(200, (int) $data['code']);
    }
}
