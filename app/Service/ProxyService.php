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
/**
 *
 * 新 代理模板处理   一级
 * @author
 * @copyright kuaishou by KS
 *
 */

use App\Model\UsersInviteReceiveLog;
use App\Model\Member;
use App\Model\Order;
use Carbon\Carbon;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use App\Service\MemberInviteStateService;
/**
 * Class ProxyService
 * @package service
 */
class ProxyService
{
    public const CACHE_KEY = 'redeem';

    public const EXPIRE = 3600;

    protected $redis;
    protected $logger;
    protected $redeem;
    protected $memberRedeemVideoService;


    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
        MemberInviteStateService $memberInviteStartService
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->memberRedeemVideoService = $memberInviteStartService;
    }
    /**
     * 加入代理返现数据
     */
    public function tuiProxyDetail(Order $order, Member $fromMember)
    {
        $flag = $this->memberRedeemVideoService->calcProxyZhi($order, $fromMember);

        co(function () use ($order, $fromMember) {
            // 异步执行，错误了不影响整体
            // invite by 代理缓存
            $invited_by = $fromMember->invited_by;
            while ($invited_by){
                /** @var \MemberModel $member */
                $member = Member::find($invited_by);
                Member::clearFor($member);
                $invited_by = $member->invited_by;
                if(!$invited_by){
                    break;
                }
            }
        });
        return $flag;
    }

    static function clearCache($aff)
    {
        redis()->del('proxy:total:' . $aff);
        redis()->del("proxy:{$aff}:0");
        redis()->del("proxy:{$aff}:1");
        redis()->del("proxy:{$aff}:2");
    }

    /**
     * 我的推广收入统计
     * @param $aff
     * @param array $condition
     * @return mixed
     */
    static function getMyProxyAmount($aff, $condition = [],$anys = 'reach_amount')
    {
        $condition[] = ['invite_by', '=', $aff];
        return UsersInviteReceiveLog::where($condition)->sum($anys);
    }
    static function getMyProxyNumber($aff, $condition = [])
    {
        $condition[] = ['invite_by', '=', $aff];
        return UsersInviteReceiveLog::where($condition)->count('id');
    }

    /**
     * 我的今日总推广收益
     * @param $aff
     * @return mixed
     */
    static function getMyTodayProxyAmountTotal($aff)
    {
        return self::getMyProxyAmount($aff, [
            ['created_date', '>=', date('Y-m-d 00:00:00')]
        ]);
    }


    /**
     * 我的累计总推广收益
     * @param $aff
     * @param \UsersInviteStatModel|null $inviteStat
     * @return float|string
     */
    static function getTotalAmount($aff, \UsersInviteStatModel $inviteStat = null)
    {
        /** @var \UsersInviteStatModel $state */
        is_null($inviteStat) && $inviteStat = \UsersInviteStatModel::getRow($aff);
        if (is_null($inviteStat)) {
            return 0.00;
        }
        return round($inviteStat->k_coins + $inviteStat->d_coins, 2);
    }



    /**
     * 用户邀请记录列表
     * @param string $aff
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public static function getUserInvitedList($aff, $limit = 50, $offset = 0, $page = 0)
    {
        $data = cached("invite:{$aff}:{$page}")->expired(600)
            ->serializerPHP()
            ->fetch(function () use (
            $aff,
            $offset,
            $limit
        ) {
            return \MemberModel::select(['uid','nickname', 'is_reg', 'regdate'])
                ->where('invited_by', $aff)
                ->orderByDesc('uid')
                ->offset($offset)
                ->limit($limit)
                ->get()->map(function ($item) {
                    $item->code = generate_code($item->uid);
                    $item->regdate_str = date('Y-m-d H:i', $item->regdate);
                    unset($item->uid);
                    return $item;
                })
                ->values();
        });

        return $data ? $data : [];
    }


}
