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
use App\Service\MemberService;
use App\Model\Member;

/**
 * @internal
 * @coversNothing
 */
class MemberServiceTest extends HttpTestCase
{
   /**
   * @var Client
   */
  protected $client;
  protected $redis;
  protected $member;

  public function __construct($name = null, 
                              array $data = [], 
                              $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->member = make(MemberService::class);
    }

    //測試會員追蹤
    public function testGetMember()
    {
      $mem = Member::where("aff","!=","")->first();
      $res = $this->member->getMember($mem->id);
      $this->assertSame($mem->id, $res["id"]);
    }
  
    //測試會員detail
    public function testGetMemberApi()
    {
      $mem = Member::where("aff","!=","")->first();
      $memberId = $mem->id;
      $user = Member::where('id',$memberId)->first();
      $token = auth()->login($user);
      make(MemberService::class)->saveToken($user->id, $token);
      $data = $this->client->post('/api/member/detail', [
      ], [
          'Authorization' => 'Bearer ' . $token,
      ]);
      $this->assertSame(200, $data['code']);
    }
}
