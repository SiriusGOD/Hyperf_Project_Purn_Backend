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

use App\Model\Member;
use App\Model\MemberInviteReceiveLog;
use App\Model\MemberInviteStat;
use App\Model\Order;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class MemberInviteStatService extends BaseService
{
    /**
     * 代理等级&规则
     * 收益来源：直推收益 | 跨级收益
     * 直推收益
     * 直推收益定义：当您成为“最强王者”时，您直推的所有下级充值，皆可获得70%分成 其他等级类推
     * --------------------
     * 跨级收益
     * 每跨一个层级，可拿直属下级底下的所有会员充值7%的分成，最多只能拿4级，最高28%
     * 当下级大于等于上级时，不再享有跨级收益.
     */
    public const LEVEL_1 = 1; // 荣耀黄金

    public const LEVEL_2 = 2; // 尊贵铂金

    public const LEVEL_3 = 3; // 永恒钻石

    public const LEVEL_4 = 4; // 至尊星耀

    public const LEVEL_5 = 5; // 最强王者

    public const LEVEL = [
        self::LEVEL_5 => [
            'rate' => 0.70,
            'name' => '钻石级',
            'vip' => 400,
            'level_rule' => [
                'pre_level' => 4, // 上一级
                'number' => 2, // 限额
            ],
        ], // 累计直推500付费会员+2直属至尊星耀以上代理
        self::LEVEL_4 => [
            'rate' => 0.64,
            'name' => '铂金级',
            'vip' => 100,
            'level_rule' => [
                'pre_level' => 2, // 上一级
                'number' => 2, // 限额
            ],
        ], // 累计直推150付费会员+2直属永恒钻石以上代理
        self::LEVEL_3 => [
            'rate' => 0.58,
            'name' => '黄金级',
            'vip' => 50,
            'level_rule' => [
                'pre_level' => 2, // 上一级
                'number' => 2, // 限额
            ],
        ], // 累计直推50付费会员+2直属尊贵铂金以上代理
        self::LEVEL_2 => [
            'rate' => 0.52,
            'name' => '白银级',
            'vip' => 20,
            'level_rule' => [
                'pre_level' => 1, // 上一级
                'number' => 2, // 限额
            ],
        ], // 累计直推5付费会员+2直属荣耀黄金以上代理
        self::LEVEL_1 => [
            'rate' => 0.46,
            'name' => '青铜级',
            'vip' => 5,
            'level_rule' => [
                'pre_level' => 0, // 上一级
                'number' => 0, // 限额
            ],
        ], // 累计直推1付费会员
    ];

    public const RATE_GAP = 0.06; // 跨级分层收益固定比例

    public $timestamps = false;

    protected $loggerFactory;

    protected $redis;

    protected $redeem;

    protected $memberInviteStateService;

    /**
     * class UsersInviteStatModel.
     *
     * @property int $id
     * @property int $uid
     * @property int $differ_num 不同人直推付费计数
     * @property int $invite_by
     * @property string $d_coins
     * @property string $k_coins
     * @property int $level 0 默认 1黄金 2 铂金 3钻石 4 星耀 5 王者
     * @property string $created
     * @property string $level_str
     *
     * @date 2022-04-01 17:38:01
     *
     * @mixin \Eloquent
     */
    protected $table = 'users_invite_stat';

    protected $primaryKey = 'id';

    protected $fillable = ['uid', 'd_coins', 'k_coins', 'level', 'differ_num', 'created'];

    protected $guarded = 'id';

    protected $appends = ['level_str'];

    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->redis = $redis;
    }

    public function getLevelStrAttribute()
    {
        // $level = max(1,$this->getAttribute('level'));
        // return self::LEVEL[$level]['name'];
        return self::LEVEL[self::LEVEL_1]['name'];
    }

    /**
     * @param mixed $uid
     * @return null|\Illuminate\Database\Eloquent\Builder|Model|object
     */
    public function getRow($uid)
    {
        return Member::where('aff', $uid)->first();
    }

    /**
     * @param int $level
     * @param mixed $uid
     * @return |MemberInviteStat
     */
    public function initRow($uid, $level = self::LEVEL_1)
    {
        //return MemberInviteLog::create([
        //    'uid' => $uid,
        //    'd_coins' => 0,
        //    'k_coins' => 0,
        //    'differ_num' => 0,
        //    'level' => $level,
        //    'created' => date('Y-m-d H:i:s'),
        // ]);
    }

    /**
     * 直推收益统计
     * @param Orders $order
     * @return bool
     */
    public function calcProxyZhi(Order $order, Member $fromMember)
    {
        // 检查代理是否存在
        // 检查
        // 添加代理邀请充值分层日志
        // 计算跨级收益
        // 计算当前等级
        $invite_by = $fromMember->invited_by;
        /** @var \MemberInviteStat $userStat */
        $userStat = self::getRow($invite_by);
        if (is_null($userStat)) {
            $userStat = self::initRow($invite_by);
        }

        if (MemberInviteReceiveLog::where(['order_sn' => $order->order_number])->exists()) {
            $logger = $this->loggerFactory->get('cors');
            $msg = "order_sn：{$order->order_id} 已经计算收益~";
            $logger->info(sprintf('%s ', $msg));
            return;
        }

        $orderAmount = $order->pay_amount / 100;
        $nowLevel = max($userStat->level, self::LEVEL_1);
        $nowRate = self::LEVEL[$nowLevel]['rate'];
        $reach_amount = round($orderAmount * $nowRate, 2);

        // 若不存在：不同的人付费加1 ，存在就不处理
        $exists = MemberInviteReceiveLog::where([
            'uid' => $fromMember->aff,
            'invite_by' => $fromMember->invited_by,
            'type' => MemberInviteReceiveLog::TYPE_ZHI,
        ])->exists();

        MemberInviteReceiveLog::create([
            'uid' => $fromMember->aff,
            'invite_by' => $fromMember->invited_by,
            'order_sn' => $order->order_id,
            'amount' => $orderAmount,
            'reach_amount' => $reach_amount,
            'level' => $nowLevel,
            'rate' => $nowRate,
            'type' => MemberInviteReceiveLog::TYPE_ZHI,
            'created_date' => date('Y-m-d H:i:s'),
        ]);
        $update = [];
        // $update['d_coins'] = \DB::raw("d_coins+{$reach_amount}"); // 用户被邀请的直推加收益
        // if (! $exists) {
       //     $update['differ_num'] = \DB::raw('differ_num+1');
        // }
        // $isOk = $userStat->update($update);
        // if ($isOk) {
       //     $flag = Member::where(['aff' => $fromMember->invited_by])->update([
       //         'tui_coins' => \DB::raw("tui_coins+{$reach_amount}"),
       //         'total_tui_coins' => \DB::raw("total_tui_coins+{$reach_amount}"),
       //     ]);
       //     // 记录日志
       //     $flag && \MemberCoinrecordModel::addExpend([
       //         'action' => \MemberCoinrecordModel::ACTION_IN_PROXY_ZHI,
       //         'uid' => $fromMember->uid,
       //         'desc' => "[直推收益] {$reach_amount}",
       //         'totalcoin' => $reach_amount,
       //         'reachcoin' => $reach_amount,
       //         'touid' => $fromMember->invited_by,
       //         'relation_id' => $order->id,
       //         'coin_type' => \MemberCoinrecordModel::COIN_TYPE_BLANCE,
       //     ]);
       //     self::updateStatLevel($userStat); // 因为直推有变动计算当前等级
       //     // async_task_cgi(function () use ($order, $fromMember, $userStat) {
       //     // 异步执行，错误了不影响整体
       //     UsersInviteStatModel::calcProxyKua($order, $fromMember, $fromMember->inviteMember, $userStat);
       //     // });
       //     //记录日志
       //     $flag && \MemberCoinrecord::addExpend([
       //         'action'      => MemberCoinrecord::ACTION_IN_PROXY_ZHI,
       //         'uid'         => $fromMember->uid,
       //         'desc'        => "[直推收益] {$reach_amount}",
       //         'totalcoin'   => $reach_amount,
       //         'reachcoin'   => $reach_amount,
       //         'touid'       => $fromMember->invited_by,
       //         'relation_id' => $order->id,
       //         'coin_type'   => MemberCoinrecord::COIN_TYPE_BLANCE
       //     ]);
       //     self::updateStatLevel($userStat);//因为直推有变动计算当前等级
       //     //async_task_cgi(function () use ($order, $fromMember, $userStat) {
       //         // 异步执行，错误了不影响整体
       //     self::calcProxyKua($order, $fromMember, $fromMember->inviteMember, $userStat);
       //     //});
       //     return true;
        // }
        return false;
    }

    /*
     * 跨级收益统计
     * @param OrdersModel $originOrder 来源订单
     * @param MemberModel $originMember 来源用户
     * @param MemberModel $inviteUser 待计算收益跨级用户
     * @param UsersInviteStatModel $inviteUserStat 跨级用户邀请状态
     * @param bool $flag false 第一次进入   true 循环进入
     * @return bool
     */
  //  public function calcProxyKua(
  //      Orders $originOrder,
  //      Member $originMember,
  //      Member $inviteUser,
  //      MemberInviteStat $inviteUserStat,
  //      $flag = false
  //  ) {
  //      $msg = "calcProxyKua: originMember:{$originMember->aff} inviteUser:{$inviteUser->aff} invited_by:{$inviteUser->invited_by} flag:{$flag}" . PHP_EOL;
  //      // echo $msg;
  //      $orderAmount = $originOrder->amount / 100;
  //      // 计算跨级收益  a->b->c->d  跨级收益从c开始计算  第一次 flag 判断
  //      // 计算当前等级
  //      $invite_by = $inviteUser->invited_by;
  //      if ($flag) {
  //          $invite_by = $inviteUser->aff;
  //      }
  //      if (! $invite_by) {
  //          return false;
  //      }
  //      /** @var \MemberInviteStat $userStat */
  //      $userStat = self::getRow($invite_by);
  //      if (is_null($userStat)) {
  //          $userStat = self::initRow($invite_by);
  //      }
  //      // print_r($userStat->getAttributes());
  //      $nowLevel = max($userStat->level, self::LEVEL_1);
  //      $nowRate = self::LEVEL[$nowLevel]['rate'];
  //      if ($nowLevel <= self::LEVEL_1 || $nowLevel <= $inviteUserStat->level) {
  //          // 跨级收益  默认荣耀黄金没有  因为下面没有给他算跨级
  //          // 当下级大于等于上级时，不再享有跨级收益
  //          if ($userStat->differ_num) {
  //              self::updateStatLevel($userStat);
  //          }

  //          $logger = $this->loggerFactory->get('cors');
  //          $msg = "no-next:".$msg;
  //          $logger->info(sprintf('%s ', $msg) );
  //          return false;
  //      }

   //     $reach_amount = round($orderAmount * self::RATE_GAP, 2);//固定 6% 跨级收益
   //     MemberInviteReceiveLog::create([
   //         'member_id'          => $originMember->aff,
   //         'invite_by'    => $invite_by,
   //         'order_sn'     => $originOrder->order_id,
   //         'amount'       => $orderAmount,
   //         'reach_amount' => $reach_amount,
   //         'level'        => $nowLevel,
   //         'rate'         => self::RATE_GAP,
   //         'type'         => MemberInviteReceiveLog::TYPE_KUA,
   //         'created_date' => date("Y-m-d H:i:s"),
   //     ]);
   //     //到账
   //    // $flag = Member::where(['aff' => $invite_by])->update([
   //     $flag && \MemberCoinrecordModel::addExpend([
   //         'action' => MemberCoinrecordModel::ACTION_IN_PROXY_ZHI,
   //         'uid' => $originMember->aff,
   //         'desc' => "[跨级收益] {$reach_amount}",
   //         'totalcoin' => $reach_amount,
   //         'reachcoin' => $reach_amount,
   //         'touid' => $invite_by,
   //         'relation_id' => $originMember->id,
   //         'coin_type' => MemberCoinrecordModel::COIN_TYPE_BLANCE,
   //     ]);
   //     self::updateStatLevel($userStat);
   //     /** @var MemberModel $inviteMember */
   //     $inviteMember = MemberModel::where('aff', '=', $invite_by)->first();
   //     if (is_null($inviteMember) || ! $inviteMember->invited_by || $inviteMember->build_id) {
   //     //记录日志
   //     $flag && \MemberCoinrecord::addExpend([
   //         'action'      => \MemberCoinrecord::ACTION_IN_PROXY_ZHI,
   //         'uid'         => $originMember->aff,
   //         'desc'        => "[跨级收益] {$reach_amount}",
   //         'totalcoin'   => $reach_amount,
   //         'reachcoin'   => $reach_amount,
   //         'touid'       => $invite_by,
   //         'relation_id' => $originMember->id,
   //         'coin_type'   => \MemberCoinrecord::COIN_TYPE_BLANCE
   //     ]);
   //     self::updateStatLevel($userStat);
   //     /** @var Member $inviteMember */
   //     $inviteMember = Member::where('aff', '=', $invite_by)->first();
   //     if (is_null($inviteMember) || !$inviteMember->invited_by || $inviteMember->build_id) {
   //         return false;
   //     }
   //     return self::calcProxyKua($originOrder, $originMember, $inviteMember->inviteMember, $userStat, true);
   // }

    /*
     * 大于当前代理等级的数量.
     * @return int
     */
//    function calcCurrentProxySub(MemberInviteStat $memberinviteStat)
//    {
//        return Member::select('members.invited_by', 'users_invite_stat.*')
//            ->join('users_invite_stat', 'users_invite_stat.uid', '=', 'members.aff')
//            ->where('members.invited_by', $inviteUserStat->uid)
//            ->where('users_invite_stat.level', '<=', $inviteUserStat->level)
//            ->count();
//    }

    /*
     * 更新代理等级.
     * @return bool
     */
    // function updateStatLevel(MemberInviteStat $inviteUserStat)
    // {
    //    if (is_null($inviteUserStat)) {
    //        return;
    //    }
    //    /** @var MemberInviteStat $inviteUserStat */
    //    $inviteUserStat = MemberInviteStat::find($inviteUserStat->id);
    //    $level = $inviteUserStat->level;
    //    if ($level == self::LEVEL_5) {
    //        return;
    //    }
    //    $currentNum = self::calcCurrentProxy($inviteUserStat);
    //    $s = var_export($inviteUserStat->getAttributes(), 1);
    //    $msg = "updateStatLevel:{$s} currentNum:{$currentNum}" . PHP_EOL;
    //    debugLog($msg);
    //    $need = self::LEVEL[$level + 1];
    //    if ($inviteUserStat->differ_num >= $need['vip'] && $currentNum >= $need['level_rule']['number']) {
    //        $inviteUserStat->increment('level', 1);
    //        $member = Member::find($inviteUserStat->uid);
    //        if ($member->invited_by && !$member->build_id) {
    //            $nextUserStat = self::getRow($member->invited_by);
    //            if (! is_null($nextUserStat)) {
    //                self::updateStatLevel($nextUserStat);
    //            }
    //        }
    //    }
    // }
}
