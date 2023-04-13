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
use App\Model\Redeem;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class RedeemService extends BaseService
{
    public const CACHE_KEY = 'redeem';

    public const EXPIRE = 3600;

    protected $redis;

    protected $logger;

    protected $redeem;

    protected $memberRedeem;

    protected $memberRedeemVideo;

    protected $videoService;

    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
        Redeem $redeem,
        MemberRedeem $memberRedeem,
        MemberRedeemVideo $memberRedeemVideo,
        VideoService $videoService
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->redeem = $redeem;
        $this->memberRedeem = $memberRedeem;
        $this->memberRedeemVideo = $memberRedeemVideo;
        $this->videoService = $videoService;
    }

      // 使用者兌換卷 by code
      public function getMemberRedeem(string $code, int $memberId)
      {
          return $this->memberRedeem->where('redeem_code', $code)
              ->where('member_id', $memberId)
              ->first();
      }

      // 使用者兌換卷 by code
      public function getMemberRedeemByCode(string $code, int $page)
      {
          $model = $this->memberRedeem->where('redeem_code', $code)
              ->offset(MemberRedeem::PAGE_PER * $page)
              ->limit(MemberRedeem::PAGE_PER);
          return $model->get();
      }

      // 兌換卷 by code
      public function getRedeemByCode(string $code)
      {
          $key = self::CACHE_KEY . ':ticket:' . $code;
          if ($this->redis->exists($key)) {
              $row = $this->redis->get($key);
              return json_decode($row, true);
          }

          if ($row = $this->redeem->where('code', $code)->first()) {
              if ($row->count == $row->counted) {
                  $this->redis->set($key, json_encode($row->toArray()));
                  $this->redis->expire($key, self::EXPIRE);
              }
              return $row->toArray();
          }
          return false;
      }

      // 兌換卷清單
      public function redeemList(int $page, int $status = 0)
      {
          $model = $this->redeem->where('status', $status)
              ->offset(Redeem::PAGE_PER * $page)
              ->limit(Redeem::PAGE_PER);
          return $model->get();
      }

      // 使月者兌換卷清單
      public function getMemberRedeemList(int $memberId, int $page, int $status = 0)
      {
          $model = $this->memberRedeem->where('status', $status)
              ->where('member_id', $memberId)
              ->offset(Redeem::PAGE_PER * $page)
              ->limit(Redeem::PAGE_PER);
          return $model->get();
      }

      // 取會員優惠 清單
      public function getMmemberDiscount(int $memberId)
      {
          return $this->memberRedeem->where('status', 0)
              ->where('member_id', $memberId)->get();
      }

      // 兌換影片
      public function redeemVideo(int $memberId, int $videoId)
      {
          // 取得會員優惠
          $discount = self::getMmemberDiscount($memberId);
          $videoDetail = $this->videoService->find($videoId);
          $discount = $discount->toArray();
          $videoDetail = $videoDetail->toArray();
          // VIP限免
          // self::updateMemberRedeemUsed($code, $memberId);
      }

      // 兌換卷更新使用次數
      public function updateMemberRedeemUsed(string $code, int $memberId)
      {
          Db::beginTransaction();
          try {
              $model = $this->redeem->where('redeem_code', $code)->where('member_id', $memberId);
              $model->used = $model->used + 1;
              $model->save();
              Db::commit();
          } catch (\Throwable $ex) {
              $this->logger->error('error:' . json_encode($ex));
              Db::rollBack();
              return false;
          }
      }

      // 兌換卷更新使用次數
      public function updateRedeemCounted(string $code, $redeemDetail)
      {
          Db::beginTransaction();
          try {
              $model = $this->redeem->find((int) $redeemDetail['id']);
              $model->counted = $model->counted + 1;
              if ($model->count < $model->counted + 1) {
                  $model->status = 1;
              }
              $model->save();
              Db::commit();
          } catch (\Throwable $ex) {
              $this->logger->error('error:' . json_encode($ex));
              Db::rollBack();
              return false;
          }
      }

      // 兌換代碼
      public function executeRedeemCode(string $code, int $memberId)
      {
          if ((self::checkUserRedeemCode($code, $memberId) == false) && self::checkRedeemCode($code)) {
              $redeemDetail = self::getRedeemByCode($code);
              // 兌換 次數上限
              if ((int) $redeemDetail['count'] >= (int) ($redeemDetail['counted'] + 1)) {
                  self::updateRedeemCounted($code, $redeemDetail);
                  $now = Carbon::now();
                  $model = new $this->memberRedeem();
                  $model->redeem_code = $code;
                  $model->diamond_point = $redeemDetail['diamond_point'];
                  $model->vip_days = $redeemDetail['vip_days'];
                  $model->free_watch = $redeemDetail['free_watch'];
                  $model->member_id = $memberId;
                  $model->redeem_id = $redeemDetail['id'];
                  $model->used = 0;
                  $model->status = 0;
                  $model->redeem_category_id = $redeemDetail['category_id'];
                  $model->start = $now->format('Y-m-d H:i:s');
                  if ((int) $redeemDetail['category_id'] == 1) {
                      $model->end = $now->addDays((int) $redeemDetail['vip_days'])->format('Y-m-d H:i:s');
                  } else {
                      $model->end = $now->format('Y-m-d H:i:s');
                  }
                  $model->updated_at = date('Y-m-d H:i:s');
                  $model->created_at = date('Y-m-d H:i:s');
                  $model->save();
                  return true;
              }
          } else {
              return false;
          }
      }

      // 查看code是否己被member 使用過
      public function checkUserRedeemCode(string $code, int $memberId)
      {
          return $this->memberRedeem->where('member_id', $memberId)->where('redeem_code', $code)->exists();
      }

      // 兌換代碼是否存在 或 己過期
      public function checkRedeemCode(string $code)
      {
          $key = self::CACHE_KEY . ':expired:' . $code;
          if ($this->redis->exists($key)) {
              return false;
          }
          $res = $this->redeem->where('status', 0)
              ->where('code', $code)
              ->where('end', '>=', date('Y-m-d H:i:s'))
              ->exists();
          if ($res == false) {
              $this->redis->set($key, true);
              $this->redis->expire($key, self::EXPIRE);
          }
          return $res;
      }
}
