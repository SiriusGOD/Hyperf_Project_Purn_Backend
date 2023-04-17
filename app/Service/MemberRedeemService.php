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

    protected $memberRedeemVideo;

    protected $videoService;

    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
        MemberRedeemVideo $memberRedeemVideo,
        MemberRedeem $memberRedeem
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->memberRedeem = $memberRedeem;
        $this->memberRedeemVideo = $memberRedeemVideo;
    }
    //使用者的優惠
    public function getMemberRedeemList(int $cateId ,int $status,array $memberId)
    {
      return $this->memberRedeem->where("redeem_category_id",$cateId)
                ->whereNotIn("member_id",$memberId)
                ->where("status",$status)->get();
    } 
  
    //使用者的優惠List
    public function getRedeemList(int $memberId ,int $page = 0 )
    {
      $query = $this->memberRedeem->where("member_id",$memberId);
      $query = $query->offset(MemberRedeem::PAGE_PER * $page)->limit(MemberRedeem::PAGE_PER);
      return $query->get(); 
    } 

    // 使用者的優惠是否有用過
    public function checkIsRedeem(int $videoId, array $discount)
    {
        return $this->memberRedeemVideo->where('video_id', $videoId)
            ->where('redeem_category_id', $discount['redeem_category_id'])
            ->where('member_redeem_id', $discount['id'])->exists();
    }

    // 使用者兌換卷更新使用次數
    public function memberRedeemVideo(int $videoId, array $discount)
    {
        if (! self::checkIsRedeem($videoId, $discount)) {
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
                return true;
            } catch (\Throwable $ex) {
                $this->logger->error('error:' . __LINE__ . json_encode($ex));
                Db::rollBack();
                return false;
            }
        } else {
            return true;
        }
    }
}
