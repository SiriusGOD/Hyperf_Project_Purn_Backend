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
namespace App\Task;

use App\Model\BuyMemberLevel;
use App\Model\Member;
use App\Model\MemberLevel;
use App\Service\AdvertisementService;
use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

#[Crontab(name: 'MemberLevelTask', rule: '00 05 * * *', callback: 'execute', memo: '會員等級變更任務')]
class MemberLevelTask
{
    protected Redis $redis;

    protected $service;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(AdvertisementService $service, LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
    }

    public function execute()
    {
        $now = Carbon::now()->toDateTimeString();
        // $yesterday = Carbon::now()->subDay()->toDateString();

        // 撈取所有有會員等級的會員
        $members = Member::where('status', '<', Member::STATUS['DISABLE'])->where('member_level_status', '>', MemberLevel::NO_MEMBER_LEVEL)->get();
        if (! empty($members)) {
            foreach ($members as $key => $member) {
                // Vip
                if ($member->member_level_status == MemberLevel::TYPE_VALUE['vip']) {
                    $member_level = BuyMemberLevel::where('member_id', $member->id)
                        ->where('member_level_type', MemberLevel::TYPE_LIST[0])
                        ->whereNull('deleted_at')
                        ->first();
                    if (! empty($member_level)) {
                        if ($member_level->end_time <= $now) {
                            // 移除到期會員VIP
                            $member_level->delete();

                            // 變更會員狀態
                            $up_member = Member::where('id', $member->id)->first();
                            $up_member->member_level_status = MemberLevel::NO_MEMBER_LEVEL;
                            $up_member->vip_quota = 0;
                            $up_member->save();
                        }
                    }
                } elseif ($member->member_level_status == MemberLevel::TYPE_VALUE['diamond']) {
                    // 鑽石
                    $member_level = BuyMemberLevel::where('member_id', $member->id)
                        ->where('member_level_type', MemberLevel::TYPE_LIST[1])
                        ->whereNull('deleted_at')
                        ->first();
                    if (! empty($member_level)) {
                        if ($member_level->end_time <= $now) {
                            // 移除到期會員鑽石
                            $member_level->delete();

                            // 確認是否有vip會員資格
                            $vip = BuyMemberLevel::where('member_id', $member->id)
                                ->where('member_level_type', MemberLevel::TYPE_LIST[0])
                                ->whereNull('deleted_at')
                                ->first();
                            if (! empty($vip)) {
                                if ($vip->end_time <= $now) {
                                    // vip 也超過時間
                                    $vip->delete();

                                    $status = MemberLevel::NO_MEMBER_LEVEL;
                                    $vip_quota_flag = true;
                                    $diamond_quota_flag = false;
                                } else {
                                    // vip 沒超過時間
                                    $status = MemberLevel::TYPE_VALUE['vip'];
                                    $vip_quota_flag = false;
                                    $diamond_quota_flag = true;
                                }
                            } else {
                                $status = MemberLevel::NO_MEMBER_LEVEL;
                                $vip_quota_flag = true;
                                $diamond_quota_flag = true;
                            }

                            // 變更會員狀態
                            $up_member = Member::where('id', $member->id)->first();
                            $up_member->member_level_status = $status;
                            if ($diamond_quota_flag) {
                                // 鑽石次數歸0
                                $up_member->diamond_quota = 0;
                            }
                            if ($vip_quota_flag) {
                                // vip次數歸0
                                $up_member->vip_quota = 0;
                            }
                            $up_member->save();
                        }
                    }
                }
            }
        }
    }
}
