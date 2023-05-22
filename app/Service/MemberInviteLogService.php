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
use Hyperf\DbConnection\Db;

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

    //邀請紀錄
    //STATUS = ['VISITORS' => 0, 'NOT_VERIFIED' => 1, 'VERIFIED' => 2, 'DISABLE' => 3, 'DELETE' => 4];
    public function invitedList(int $memberId ,int $page=0 ){
      $wheres = ['invited_by' => $memberId ];
      $model = $this->memberInviteLog->select("member_id");
      $res = $this->list($model , $wheres, $page, MemberInviteLog::PAGE_PER);
      $memberWhere = $res->pluck( 'member_id' )->toArray(); 
      //where member
      $createDate = " DATE_FORMAT(created_at, '%Y-%m-%d') AS reg_day";
      $sqlStatus = "CASE `status`
           WHEN 0 THEN '".trans("default.member.visitors")."'
           WHEN 1 THEN '".trans("default.member.not_verified")."'
           WHEN 2 THEN '".trans("default.member.verified")."'
           WHEN 3 THEN '".trans("default.member.disable")."'
           WHEN 4 THEN '".trans("default.member.delete")."'
           ELSE ''
       END AS `user_status`";
      $select = ["id",DB::raw($createDate),DB::raw($sqlStatus),"name"];
      $model = $this->member->select($select)
                  ->without('is_selected_tag')
                  ->whereIn('id',$memberWhere);
      if ($page == 1) {
        $page = 0;
      }
      $model = $model->offset($page * MemberInviteLog::PAGE_PER)->limit(MemberInviteLog::PAGE_PER)->get();
      return $this->removeCol( $model->toArray(), 'is_selected_tag');
    }
}
