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
use App\Service\RedeemService;
use App\Service\MemberRedeemService;
use App\Service\MemberService;
use App\Service\VideoService;
use Hyperf\Redis\Redis;
/**
 * @internal
 * @coversNothing
 */
class RedeemApiTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $redeem;
    protected $memberRedeem;
    protected $video;
    protected $redis;
    protected $token;
  
    protected $testUserId = 1;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->redeem = make(RedeemService::class);
        $this->memberRedeem = make(MemberRedeemService::class);
        $this->video = make(VideoService::class);
        $this->redis = make(Redis::class);
    }

    //取可用兌換卷
    public function testMemberRedeemApiFalse()
    {
      $memberId = 7;
      $user = Member::where('id',$memberId)->first();
      $token = auth()->login($user);
      $this->token = $token; 
      make(MemberService::class)->saveToken($user->id, $token);
      $data = $this->client->post('/api/redeem/videoRedeem', [
          'video_id' => 1
      ], [
          'Authorization' => 'Bearer ' . $token,
      ]);
      $this->assertSame(true, empty($data['data']['models']));
    }
  
    //取可用兌換卷
    public function testMemberRedeemApi()
    {
      $user = Member::where('id',1)->first();
      $token = auth()->login($user);
      $this->token = $token; 
      make(MemberService::class)->saveToken($user->id, $token);
      $data = $this->client->post('/api/redeem/videoRedeem', [
          'video_id' => 1
      ], [
          'Authorization' => 'Bearer ' . $token,
      ]);
      $this->assertSame(200, (int)$data['code']);
    }
  
    //取可用兌換卷
    public function test1MemberRedeemApiList()
    {
      $user = Member::where('id',1)->first();
      $token = auth()->login($user);
      $this->token = $token; 
      make(MemberService::class)->saveToken($user->id, $token);
      $data = $this->client->get('/api/redeem/videoRedeemList',
      [
        "page" => 0
      ], 
      [
          'Authorization' => 'Bearer ' . $token,
      ]);
      $this->assertSame(1, (int)$data['data']['models'][0]["member_id"]);
    }

    //取可用兌換卷
    public function testUsedMemberRedeemApiList()
    {
      $memberId = 1;
      $user = Member::where('id',$memberId)->first();
      $token = auth()->login($user);
      $this->token = $token; 
      make(MemberService::class)->saveToken($user->id, $token);
      $data = $this->client->get('/api/redeem/usedVideoRedeemList',
      [
        "page" => 0
      ], 
      [
          'Authorization' => 'Bearer ' . $token,
      ]);
      $this->assertSame($memberId, (int)$data['data']['models'][0]["member_id"]);
    }
}
