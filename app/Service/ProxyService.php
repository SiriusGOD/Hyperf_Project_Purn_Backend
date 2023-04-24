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
use App\Model\MemberInviteReceiveLog;
use App\Model\Order;
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

    protected $memberInviteStatService;

    protected $memberRedeemVideoService;

    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
        MemberInviteStatService $memberInviteStatService
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
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
