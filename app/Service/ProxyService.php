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
use App\Model\Member;
use App\Model\MemberInviteLog;
use App\Model\MemberInviteReceiveLog;
use App\Model\Order;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

/**
 * Class ProxyService.
 */
class ProxyService extends BaseService
{
    public const CACHE_KEY = 'redeem';

    public const EXPIRE = 3600;

    public const LIMIT = 10;

    protected $redis;

    protected $logger;

    protected $redeem;

    protected $member;

    protected $memberInviteReceiveLog;

    protected $memberInviteStatService;

    protected $memberInviteLog;

    protected $memberRedeemVideoService;
    protected $memberService;

    public function __construct(
        Redis $redis,
        Member $member,
        LoggerFactory $loggerFactory,
        MemberInviteLog $memberInviteLog,
        MemberInviteReceiveLog $memberInviteReceiveLog,
        MemberInviteStatService $memberInviteStatService
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->member = $member;
        $this->memberInviteLog = $memberInviteLog;
        $this->memberInviteReceiveLog = $memberInviteReceiveLog;
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
    // 計算層級代理有多少收益
    public function calcLevelInCome(int $memberId, int $invited_by, int $level)
    {
        $page = 1;
        $limit = self::LIMIT;
        $where['member_id'] = $memberId;
        $where['invite_by'] = $invited_by;
        $where['level'] = $level;
        $model = $this->memberInviteReceiveLog;
        $res = $this->list($model, $where, $page, $limit);
        $sumAry = $res->toArray();
        if ($res->count() > 0) {
            return array_reduce($sumAry, function ($carry, $item) {
                return $carry + $item['reach_amount'];
            }, 0);
        }
        return false;
    }

    // 我的下線by level
    public function calcLevel(int $memberId, int $level)
    {
        $page = 1;
        $limit = self::LIMIT;
        $where['member_id'] = $memberId;
        $where['level'] = $level;
        $model = $this->memberInviteLog;
        $res = $this->list($model, $where, $page, $limit);
        if ($res->count() > 0) {
            return self::calcLevelInCome($res[0]->invited_by, $res[0]->member_id, $level);
        }
        return false;
    }

    // 我的代理統計 -收益
    public function incomeTotal(int $memberId)
    {
        $query = $this->memberInviteReceiveLog->select(Db::raw('SUM(reach_amount) as total_income'))
                ->where('member_id', $memberId);
        $result = $query->first();
        return $result->total_income;
    }

    // 我的代理統計
    public function downlintTotal(int $memberId):array
    {
        $query = $this->memberInviteLog
                ->select('level', Db::raw('COUNT(*) as count'))
                ->where('invited_by', $memberId)
                ->whereIn('level', [1, 2, 3, 4])
                ->groupBy('level');        
        $results = $query->get();
        $r=[];
        foreach ($results as $result) {
            $level = $result->level;
            $r[$level] =$result->count;
        }
        if( count($r)!=4){
          for($i = count($r)+1 ; $i<= 4 ; $i++){
            $r[$i] = 0;
          }
        }
        return $r;
    }
    // 我的下線
    public function downline(int $memberId, int $page)
    {
        $limit = self::LIMIT;
        $where['invited_by'] = $memberId;
        $model = $this->memberInviteLog
           ->with(['member' => function ($query) {
            $query->select('id', 'name');
        }]);
        return $this->list($model, $where, $page, $limit);
    }

    // 我的收益
    public function myIncome(int $memberId, int $page)
    {
        $limit = self::LIMIT;
        $where['member_id'] = $memberId;
        $levelSql = "CASE member_invite_receive_log.level
           WHEN 1 THEN '".trans("default.proxy.lv1")."'
           WHEN 2 THEN '".trans("default.proxy.lv2")."'
           WHEN 3 THEN '".trans("default.proxy.lv3")."'
           WHEN 4 THEN '".trans("default.proxy.lv4")."'
           ELSE ''
       END AS proxy_level ";
        $payDate = " DATE_FORMAT(member_invite_receive_log.created_at, '%Y.%m.%d') AS date ";
        $result = $this->memberInviteReceiveLog
            ->select( 'member_invite_receive_log.product_name',DB::raw($payDate),DB::raw($levelSql), 'member_invite_receive_log.reach_amount',  'members.name as member_name')
            ->leftJoin('members', 'member_invite_receive_log.member_id', '=', 'members.id')
            ->where('member_invite_receive_log.member_id', $memberId)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();
        return $result; 
    }

    // 返傭
    public function rebate(Member $member, Order $order , $product)
    {
        Db::beginTransaction();
        $wg = new \Hyperf\Utils\WaitGroup();
        $memberInviteReceiveLog = $this->memberInviteReceiveLog;
        $memberModel = $this->member;
        // 商品類型不是現金點數
        // 怕使用者 充了數馬上提出
        try {
            // if ($product->type != Product::TYPE_LIST[1]) {
            //買影片之類的用現金點數扣
            $money = $order->pay_amount;
            // 查看上層代理
            $res = $this->memberInviteLog->where('member_id', $member->id)->get();
            $rate = self::calculatePercentage($money);
            foreach ($res as $proxy) {
                $userLevel = $proxy->level;
                $uRate = ProxyCode::LEVEL[$userLevel]['rate'];
                $amount = number_format($money * $uRate * $rate, 2);
                // print_r(['amount'=>$amount]);
                $imem = $this->memberService->getMember($proxy->invited_by);
                $return['member_name'] = $imem->name;
                $return['member_id'] = $proxy->invited_by;
                $return['invite_by'] = $proxy->member_id;
                $return['order_sn'] = $order->order_number;
                $return['amount'] = $money;
                $return['reach_amount'] = $amount;
                $return['product_name'] =  $product["name"];
                $return['level'] = $userLevel;
                $return['rate'] = $uRate;
                $return['type'] = ($proxy->level == 1) ? 0 : 1; // 0 直推 1 跨级收益
                $wg->add(1);
                // 返傭
                co(function () use ($wg, $return,$memberInviteReceiveLog, $memberModel, $amount) {
                    try {
                        $member = $memberModel->find((int) $return['member_id']);
                        $member->coins = $member->coins + $amount;
                        $member->save();
                        $memberInviteReceiveLog->create($return);
                        usleep(100);
                        $wg->done();
                    } catch (\Throwable $ex) {
                        $this->logger->error($ex->getMessage());
                        throw $ex;
                    } finally {
                        Db::rollBack();
                    }
                });
            }
            // }
            $wg->wait();
            Db::commit();
        } catch (\Throwable $ex) {
            $this->logger->error($ex->getMessage());
            Db::rollBack();
            return false;
        }
    }

    // 分潤計算
    public function calculatePercentage($money)
    {
        if ($money <= 1000) {
            return 0.1;
        }
        if ($money <= 2000) {
            return 0.12;
        }
        if ($money <= 5000) {
            return 0.14;
        }
        if ($money <= 10000) {
            return 0.16;
        }
        if ($money <= 20000) {
            return 0.18;
        }
        if ($money <= 40000) {
            return 0.20;
        }
        if ($money <= 70000) {
            return 0.23;
        }
        if ($money < 100000) {
            return 0.26;
        }
        return 0.30;
    }

    /**
     *分潤計算
     * 返佣.
     */
    public function returnRateMoney(float $money, int $userLevel)
    {
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
