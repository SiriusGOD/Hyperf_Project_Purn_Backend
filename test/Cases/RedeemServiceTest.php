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
use App\Service\MemberService;
use App\Service\VideoService;

/**
 * @internal
 * @coversNothing
 */
class RedeemServiceTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $redeem;
    protected $video;
  
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->redeem = make(RedeemService::class);
        $this->video = make(VideoService::class);
    }

    //測試 get  redeem by code
    public function testGetRedeemByCode()
    {
      $res = self::getStatus0Redeem();
      $code = $res->toArray()[0]["code"];
      $row = $this->redeem->getRedeemByCode($code);
      $this->assertSame($code , $row->toArray()["code"]);
    }

    //取可用兌換卷
    public function getStatus0Redeem()
    {
      $status = 0;
      return $this->redeem->redeemList(0, $status);
    }

    //取影片列表
    public function getVideoList()
    {
      return $this->video->getVideos([], 0);
    }

    //測試redeem list
    public function testRedeemList()
    {
      $res = self::getStatus0Redeem();
      $this->assertSame(0,$res->toArray()[0]["status"]);
    }

    //測試redeem list
    public function testUserRedemption()
    {
      $videos = self::getVideoList();
      $redeems = self::getStatus0Redeem();
      $user = Member::first();
      $token = auth()->login($user);
      make(MemberService::class)->saveToken($user->id, $token);
      $videoId = $videos->toArray()[0]["id"];
      $code = $redeems->toArray()[0]["code"];
      $latestCode = $redeems->toArray()[count($redeems->toArray())-1]["code"];
      $res = $this->redeem->redeemCode($code);
      $this->assertSame(true,$res);
      $res = $this->redeem->redeemCode($latestCode);
      $this->assertSame(false,$res);
    }
}
