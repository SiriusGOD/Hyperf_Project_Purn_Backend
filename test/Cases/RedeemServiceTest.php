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
use App\Constants\RedeemCode;
use App\Constants\VideoCode;
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
    protected $memberRedeem;
    protected $video;
    protected $redis;
  
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
    //是否限免 0 免费视频 1vip视频 2金币视频
    public function getPayVideoList(int $isFree=1)
    {
      return $this->video->getPayVideos([], 0, 1, $isFree);
    }
    //取影片列表
    public function getVideoList(int $status = 0)
    {
      $page = 0;
      return $this->video->getVideos([], $page, $status);
    }

    //測試redeem list
    public function testRedeemList()
    {
      $status = 1;  
      $res = self::getStatusRedeem($status);
      $this->assertSame($status, $res->toArray()[0]["status"]);
    }
    //just show
    public function show($data){
      print_r([$data]);
    }

    //測試兌換 code 
    public function testUserRedemptionCode()
    {
      //可用
      $redeems_can = $this->redeem->redeemList(0, 0);

      $redeems_not = $this->redeem->redeemList(0, 1);
      if(count($redeems_can->toArray())>0){
      //不可用
        $user  = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        //付費影片
        $code = $redeems_can->toArray()[0]["code"];
        //不可用
        $expiredCode = $redeems_not->toArray()[0]["code"];
        $latestCode = $redeems_can->toArray()[count($redeems_can->toArray())-1]["code"];

        //查看是否兌換
        $res = $this->redeem->checkRedeemCode($code);
        $this->assertSame(true, $res);
        //過期
    
        $expiredRes = $this->redeem->checkRedeemCode($expiredCode);
        $this->assertSame(false, $expiredRes);

        //過期查看是否存在redis
        $res = $this->redis->exists("redeem:expired:".$expiredCode);
        $this->assertSame(1,$res);
        //兌換代碼 
        foreach($redeems_can->toArray() as $rede){
          $this->redeem->executeRedeemCode($rede["code"] ,$this->testUserId);
          for($i=rand(10,20) ; $i <= 90 ; $i++){
              $this->redeem->executeRedeemCode($rede["code"] ,$i);
          }
        }
        $memberRes = $this->redeem->getMemberRedeemByCode($code ,0);
        $redeemRes = $this->redeem->getRedeemByCode($code);
        $this->assertSame((int)$redeemRes["count"], count($memberRes->toArray()));
      }else{
        $this->assertSame(0, count($redeems_can->toArray()));
      }
    }

    //測試兌換member redeem list 
    public function testUserRemeemList()
    {
      $status = 0;
      $memberRedeem = $this->redeem->getMemberRedeemList($this->testUserId ,$status);
      if(count($memberRedeem->toArray())>0 ){
        $this->assertSame((int)$memberRedeem->toArray()[0]['status'], $status);
      }else{
        $this->assertSame(0, count($memberRedeem->toArray()));
      }
    }

    //測試 兌換vip diamond free影片
    public function testUserRemeemVideo()
    {
      $memberId = $this->testUserId;
      //self::userRemeemVideo($memberId ,VideoCode::VIP);
      //self::userRemeemVideo($memberId ,VideoCode::DIAMOND);
      $res = self::userRemeemVideo($memberId ,VideoCode::FREE);
      $this->assertNotSame(false, $res);
      //VideoCode::FREE
    }

    //測試只有單一兌換vip diamond free影片
    public function testUserSingleRemeemVideo()
    {
      //$redeemListVip = $this->memberRedeem->getMemberRedeemList(RedeemCode::VIP, 0 ,[1]);
      //$testMemberId = (int)$redeemListVip->toArray()[0]['member_id'];
      //self::userRemeemVideo($testMemberId ,VideoCode::VIP);

      //$redeemListVip = $this->memberRedeem->getMemberRedeemList(RedeemCode::DIAMOND, 0 ,[1]);
      //$testMemberId = (int)$redeemListVip->toArray()[0]['member_id'];
      //self::userRemeemVideo($testMemberId , VideoCode::DIAMOND);

      //$redeemListVip = $this->memberRedeem->getMemberRedeemList(RedeemCode::FREE, 0 ,[1]);
      //$testMemberId = (int)$redeemListVip->toArray()[0]['member_id'];
      //self::userRemeemVideo($testMemberId ,VideoCode::FREE);
    }

    //測試兌換member redeem video 
    public function userRemeemVideo(int $memberId ,int $videoType)
    {
      $successStatus = 0;
      $memberRedeemList = $this->redeem->getMemberRedeemList($memberId ,$successStatus);
      $memberRedeemList = $memberRedeemList->toArray();
      $memberRedeemCate = array_column($memberRedeemList , 'redeem_category_id') ;
      $tenCostVideos = self::getPayVideoList($videoType);
      //付費影片
      $lg=true;
      if(count($memberRedeemList)>0){
        foreach($tenCostVideos->toArray() as $count => $video){
          
            if($lg){
              $r=rand(1,9);
              $videoId = $tenCostVideos->toArray()[$r]["id"];
              $videoCate = $video["is_free"];
              //print_r([$memberId, $videoId, $videoType]);
              //if($this->redeem->canRedeemVideo($memberRedeemCate , $videoCate) ){
              return $redeemStatus = $this->redeem->redeemVideo($memberId, $videoId);
              //print_r([$memberId,  $redeemStatus]);
              //測試 status 是否一至
              //$this->assertSame(true, $redeemStatus);
              //}

              $lg = false;
            }
        }
      }
    }
}
