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

use App\Model\MemberRedeem;
use App\Model\MemberRedeemVideo;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class MemberRedeemService extends BaseService
{
    public const CACHE_KEY = 'member_redeem';

    public const EXPIRE = 3600;

    protected $redis;

    protected $logger;

    protected $memberRedeem;

    protected $videoService;

    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
        MemberRedeem $memberRedeem
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->memberRedeem = $memberRedeem;
    }

    // 使用者兌換卷更新使用次數
    public function memberRedeemVideo(int $videoId, array $discount)
    {
        Db::beginTransaction();
        try {
            $memberRedeemVideo = new MemberRedeemVideo();
            $memberRedeemVideo->member_redeem_id = $discount['id'];
            $memberRedeemVideo->video_id = $videoId;
            $memberRedeemVideo->redeem_category_id = $discount['redeem_category_id'];
            $memberRedeemVideo->member_id = $discount['member_id'];
            $memberRedeemVideo->updated_at = date('Y-m-d H:i:s');
            $memberRedeemVideo->created_at = date('Y-m-d H:i:s');
            $memberRedeemVideo->save();

            $model = $this->memberRedeem
                ->where('redeem_code', $discount['redeem_code'])
                ->where('member_id', $discount['member_id'])->first();
            $model->used = $model->used + 1;
            if ($discount['redeem_category_id'] == 2) {
                if (($model->diamond_point - 1) == 0) {
                    $model->status = 1;
                }
                $model->diamond_point = $model->diamond_point - 1;
            }
            if ($discount['redeem_category_id'] == 3) {
                if (($model->free_watch - 1) == 0) {
                    $model->status = 1;
                }
                $model->free_watch = $model->free_watch - 1;
            }
            $model->save();
            Db::commit();
        } catch (\Throwable $ex) {
            $this->logger->error('error:' . __LINE__ . json_encode($ex));
            Db::rollBack();
            return false;
        }
    }
}
