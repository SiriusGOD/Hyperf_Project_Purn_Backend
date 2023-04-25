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
namespace App\Service;

use App\Constants\ProxyCode;
use App\Model\MemberInviteReceiveLog;
use App\Model\MemberInviteLog;
use App\Model\Member;
use App\Model\Order;
use App\Model\Product;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

/**
 * Class ProxyService.
 */
class ProxyService
{
    public const CACHE_KEY = 'redeem';

    public const EXPIRE = 3600;

    protected $redis;

    protected $logger;

    protected $redeem;
    protected $member;
    protected $memberInviteStatService;
    protected $memberInviteLog;
    protected $memberRedeemVideoService;
    protected $memberInviteReceiveLog;

    public function __construct(
        Redis $redis,
        Member $member,
        LoggerFactory $loggerFactory,
        MemberInviteLog $memberInviteLog,
        MemberInviteReceiveLog $memberInviteReceiveLog,
        MemberInviteStatService $memberInviteStatService
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis =  $redis;
        $this->member = $member;
        $this->memberInviteLog = $memberInviteLog;
        $this->memberInviteReceiveLog =$memberInviteReceiveLog;
        $this->memberInviteStatService = $memberInviteStatService;
    }

    /**
     * 加入代理返现数据.
     * @param mixed $money
     */
    // public function tuiProxyDetail(Order $order, Member $fromMember)
    // {
    //    $flag = $this->memberInviteStatService->calcProxyZhi($order, $fromMember);
    //    co(function () use ($fromMember) {
    //        // 异步执行，错误了不影响整体
    //        // invite by 代理缓存
    //        $invited_by = $fromMember->invited_by;
    //        while ($invited_by) {
    //            /** @var \MemberModel $member */
    //            $member = Member::find($invited_by);
    //            //Member::clearFor($member);
    //            $invited_by = $member->invited_by;
    //            if (! $invited_by) {
    //                break;
    //            }
    //        }
    //    });
    //    return $flag;
  
    //我的下線
    public function downline(int $memberId ,int $page )
    {
      $limit = 10;
      if($page==1){
        $page = $$page-1;
      }
      return $this->memberInviteLog 
                          ->where('member_id',$memberId)
                          ->offset($page * $limit)
                          ->limit($limit)
                          ->get();
    }
    //我的收益
    public function myIncome(int $memberId ,int $page )
    {
      $limit = 10;
      if($page==1){
        $page = $$page-1;
      }
      return $this->memberInviteReceiveLog
                          ->where('member_id',$memberId)
                          ->offset($page * $limit)
                          ->limit($limit)
                          ->get();
    }
    //返傭
    public function rebate(Member $member ,Order $order, Product $product)
    {
      $wg = new \Hyperf\Utils\WaitGroup();
      $memberInviteReceiveLog = $this->memberInviteReceiveLog;
      $memberModel = $this->member;
      //商品類型不是現金點數
      //怕使用者 充了數馬上提出
      if($product->type != Product::TYPE_LIST[1]){
        $money = $order->pay_amount;
        //查看上層代理
        $res = $this->memberInviteLog->where("member_id", $member->id)->get();
        $rate = self::calculatePercentage($money);
        foreach($res as $proxy){
          
          $userLevel = $proxy->level; 
          $uRate = ProxyCode::LEVEL[$userLevel]['rate'];
          $amount = number_format($money * $uRate *  $rate ,2);
          //print_r(['amount'=>$amount]);
          $return["member_id"] = $proxy->invited_by;
          $return["invite_by"] = 0;
          $return["order_sn"] = $order->order_number;
          $return["amount"] = number_format($money ,2);
          $return["reach_amount"] = number_format($money ,2);
          $return["level"] = $userLevel;
          $return["rate"] = $uRate;
          $return["type"] = ($proxy->leverl == 1) ? 0 : 1 ;//0 直推 1 跨级收益

          $wg->add(1);
          //返傭
          co(function() use ($wg, $return, $memberInviteReceiveLog, $memberModel, $amount){
            $member = $memberModel->find((int)$return["member_id"]);
            $member->coins = $member->coins + $amount; 
            $member->save(); 
            $memberInviteReceiveLog->create($return);
            //usleep(100);
            $wg->done();
          });
        } 
      }
      $wg->wait(); 

    }  

    //分潤計算 
    public function calculatePercentage($money) {
      if ($money <=1000) {
          return 0.1;
      } elseif ($money <=2000) {
          return 0.12;
      } elseif ($money <= 5000) {
          return 0.14;
      } elseif ($money <= 10000) {
          return 0.16;
      } elseif ($money <= 20000) {
          return 0.18;
      } elseif ($money <= 40000) {
          return 0.20;
      } elseif ($money <= 70000) {
          return 0.23;
      } elseif ($money < 100000) {
          return 0.26;
      } else {
          return 0.30;
      }
    }
    /**
     *分潤計算 
     * 返佣
     */ 
    public function returnRateMoney(float $money ,int $userLevel){
        $res = self::calculatePercentage($money);
        return $res * $money * ProxyCode::LEVEL[$userLevel]['rate'];
    } 

    /*
     * 我的推广收入统计
     * @param array $condition
     * @param mixed $anys
     * @return mixed
     */
    // public function getMyProxyAmount($aff, $condition = [], $anys = 'reach_amount')
    // {
    //    $condition[] = ['invite_by', '=', $aff];
    //    return MemberInviteReceiveLog::where($condition)->sum($anys);
    // }

  //  public function getMyProxyNumber($aff, $condition = [])
  //  {
  //      $condition[] = ['invite_by', '=', $aff];
  //      return MemberInviteReceiveLog::where($condition)->count('id');
  //  }

    /*
     * 我的今日总推广收益.
     * @return mixed
     */
    // public function getMyTodayProxyAmountTotal($aff)
    // {
    //    return self::getMyProxyAmount($aff, [
    //        ['created_date', '>=', date('Y-m-d 00:00:00')],
    //    ]);
    // }

    /*
     * 我的累计总推广收益.
     * @return float|string
     */
    // public function getTotalAmount($aff, MemberInviteStatService $service )
    // {
    //    /** @var \UsersInviteStatModel $state */
    //    is_null($inviteStat) && $inviteStat = $service->getRow($aff);
    //    if (is_null($inviteStat)) {
    //        return 0.00;
    //    }
    //    return round($inviteStat->k_coins + $inviteStat->d_coins, 2);
    // }

    /*
     * 用户邀请记录列表.
     * @param string $aff
     * @param int $offset
     * @param int $limit
     * @param mixed $page
     * @return array
     */
    // function getUserInvitedList($aff, $limit = 50, $offset = 0, $page = 0)
    // {

    //  return Member::query()
    //      ->select(['id', 'nickname', 'is_reg', 'regdate'])
    //      ->where('invited_by', $aff)
    //      ->orderByDesc('id')
    //      ->offset($offset)
    //      ->limit($limit)
    //      ->get()
    //      ->map(function ($item) {
    //          $item->code = generate_code($item->uid);
    //          $item->regdate_str = date('Y-m-d H:i', $item->regdate);
    //          unset($item->uid);
    //          return $item;
    //      })
    //      ->values();
    // }
}
