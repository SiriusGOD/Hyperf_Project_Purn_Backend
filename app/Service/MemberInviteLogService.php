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
use App\Model\MemberInviteLog;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class MemberInviteLogService extends BaseService
{
    protected $loggerFactory;

    protected $redis;

    protected $redeem;

    protected $member;

    protected $memberInviteLog;

    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
        MemberInviteLog $memberInviteLog,
        Member $member
    ) {
        $this->memberInviteLog = $memberInviteLog;
        $this->loggerFactory = $loggerFactory;
        $this->redis = $redis;
        $this->member = $member;
    }

    /**
     * @param mixed $aff
     * @return null|\Illuminate\Database\Eloquent\Builder|Model|object
     */
    public function getRow($aff)
    {
        return Member::where('aff', $aff)->first();
    }

    /**
     * @param int $level
     * @return |MemberInviteStat
     */
    public function initRow(array $datas)
    {
        $model = $this->memberInviteLog;
        $this->modelStore($model, $datas);
    }

    // 計算每一層代理
    public function calcProxy(Member $member)
    {
        $level = 2;
        $memberModel = $this->member;
        $invitedModel = $this->memberInviteLog;
        $wg = new \Hyperf\Utils\WaitGroup();
        $old = $member;
        $wg->add(1);
        co(function () use ($wg, $memberModel, $level, $invitedModel, $member, $old) {
            while ($level <= 4 && $member->invited_by) {
                $member = $memberModel->find($member->invited_by);
                $data = [
                    'member_id' => $old->id,
                    'invited_code' => '',
                    'invited_by' => $member->invited_by,
                    'level' => $level,
                ];
                $invitedModel->create($data);
                $level = $level + 1;
            }
            $wg->done();
        });
        $wg->wait();
    }
}
