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
use Hyperf\Redis\Redis;
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
    protected $redis;
  
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->redeem = make(RedeemService::class);
        $this->video = make(VideoService::class);
        $this->redis = make(Redis::class);
    }

    //測試 get  redeem by code
    public function testGetRedeemByCode()
    {
      $res = self::getStatusRedeem(1);
      $code = $res->toArray()[0]["code"];
      $row = $this->redeem->getRedeemByCode($code);
      $this->assertSame($code , $row["code"]);
    }

    //取可用兌換卷
    public function getStatusRedeem(int $status = 0)
    {
      return $this->redeem->redeemList(0, $status);
    }

    //取影片列表
    public function getCoseVideoList()
    {
      $payStatus = 1;
      $page = 0;
      return $this->video->getVideos([], $page, $payStatus);
    }

    //測試redeem list
    public function testRedeemList()
    {
      $res = self::getStatusRedeem(1);
      $this->assertSame(1,$res->toArray()[0]["status"]);
    }
    //just show
    public function show($data){
      print_r([$data]);
    }

    //測試兌換 code 
    public function testUserRedemptionCode()
    {
      //可用
      $redeems_can = self::getStatusRedeem(0);
      if(count($redeems_can)){
        $user  = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);

        //付費影片
        $code = $redeems_can->toArray()[0]["code"];

        //不可用
        $redeems_not = self::getStatusRedeem(1);
        $expiredCode = $redeems_not->toArray()[0]["code"];
        $latestCode = $redeems_can->toArray()[count($redeems_can->toArray())-1]["code"];

        //查看是否兌換
        $res = $this->redeem->checkRedeemCode($code);
        $this->assertSame(true, $res);
        //過期
        $expiredRes = $this->redeem->checkRedeemCode($expiredCode);
        $this->assertSame(false,$expiredRes);

        //過期查看是否存在redis
        $res = $this->redis->exists("redeem:expired:".$expiredCode);
        $this->assertSame(1,$res);
        //兌換代碼 
       
        for($i=1 ; $i <= 30 ; $i++){
            $this->redeem->executeRedeemCode($code ,$i);
        }

        $memberRes = $this->redeem->getMemberRedeemByCode($code ,0);
        $redeemRes = $this->redeem->getRedeemByCode($code);
        $this->assertSame((int)$redeemRes["count"], count($memberRes->toArray()));
      }else{
        $this->assertSame(count($redeems_can), 0 );
      }
    }

    //測試兌換member redeem list 
    public function testUserRemeemList()
    {
      $status = 0;
      $memberRedeem = $this->redeem->getMemberRedeemList(1 ,$status);
      $this->assertSame((int)$memberRedeem->toArray()[0]['status'], $status);
    }

    //測試兌換member redeem video 
    public function testUserRemeemVideo()
    {
      $status = 0;
      $memberId = 1;
      $memberRedeemList = $this->redeem->getMemberRedeemList($memberId ,$status);
      //付費影片
      $costVideos = self::getCoseVideoList();
      self::show( $costVideos->toArray() );
      $videoId = $costVideos->toArray()[0]["id"];
      $this->assertSame((int)$memberRedeemList->toArray()[0]['status'], $status);
      self::show($memberRedeemList->toArray());
      $this->redeem->redeemVideo($memberId, $videoId);
    }
}
