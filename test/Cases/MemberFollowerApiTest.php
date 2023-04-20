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

/**
 * @internal
 * @coversNothing
 */
class MemberFollowerApiTest extends HttpTestCase
{
   /**
   * @var Client
   */
  protected $client;
  protected $redis;

  public function __construct($name = null, 
                              array $data = [], 
                              $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
    }

    //測試會員追蹤
    public function testMemberApi()
    {
      $memberId = 1;
      $user = Member::where('id',$memberId)->first();
      $token = auth()->login($user);
      make(MemberService::class)->saveToken($user->id, $token);
      $data = $this->client->post('/api/member/addMemberIdsFollow', [
          'ids' => [1,2,3,5,9,11,15],
          'type' => "tag",
      ], [
          'Authorization' => 'Bearer ' . $token,
      ]);
      $this->assertSame(200, $data['code']);
    }
  
}
