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
use App\Service\MemberService;
use HyperfTest\HttpTestCase;
/**
 * @internal
 * @coversNothing
 */
class EncTest extends HttpTestCase
{



    public function testUrl()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data = $this->client->post('/api/order/list', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        print_r([$data]);
        $this->assertSame(200, (int)$data['code']);
    }
}
