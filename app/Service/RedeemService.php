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

use App\Constants\RedeemCode;
use App\Model\MemberRedeem;
use App\Model\Redeem;
use Carbon\Carbon;
use Hyperf\Database\Model\Collection;
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
    protected $memberRedeemService;
    protected $memberRedeemVideoService;
    protected $videoService;

    public function __construct(
        Redis $redis,
        LoggerFactory $loggerFactory,
        Redeem $redeem,
        MemberRedeem $memberRedeem,
        MemberRedeemService $memberRedeemService,
        MemberRedeemVideoService $memberRedeemVideoService,
        VideoService $videoService
    ) {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->redeem = $redeem;
        $this->memberRedeem = $memberRedeem;
        $this->memberRedeemService = $memberRedeemService;
        $this->memberRedeemVideoService = $memberRedeemVideoService;
        $this->videoService = $videoService;
    }

      // 使用者兌換卷 by code
      public function getMemberRedeem(string $code, int $memberId)
      {
          return $this->memberRedeem->where('redeem_code', $code)
              ->where('member_id', $memberId)
              ->first();
      }

      // 取得兌換卷
      public function find(int $id)
      {
          return $this->redeem->find($id);
      }

      // store兌換卷
      public function store(array $datas)
      {
          if (empty($datas['id'])) {
              unset($datas['id']);
          }
          if (Redeem::where('id', $datas['id'])->exists()) {
              $model = Redeem::where('id', $datas['id'])->first();
          } else {
              $model = new Redeem();
          }
          foreach ($datas as $key => $val) {
              $model->{$key} = $val;
          }
          $model->category_name = RedeemCode::CATEGORY[$model->category_id];

          if ($model->save()) {
              return true;
          }
          return false;
      }

      // 檢查兌換卷
      public function checkRedeem(string $code ,int $memberId)
      {
          if ((self::checkUserRedeemCode($code, $memberId))){
            return ["is_used"=>1 ,'status'=>2];
          }

          $today = Carbon::now()->toDateTimeString();  
          $model = $this->redeem->where('status', RedeemCode::ABLE)
                                ->where('code', $code)
                                ->where('end','>=', $today)->first();
          if($model){
            return ["is_used"=>0,'status'=>0 ,'model'=>$model] ;
          }
          return ["is_used"=>0,'status'=>1 ] ;
      }
      // 使用者兌換卷 by code
      public function getMemberRedeemByCode(string $code, int $page)
      {
          $model = $this->memberRedeem->where('redeem_code', $code)
              ->offset(Redeem::PAGE_PER * $page)
              ->limit(Redeem::PAGE_PER);
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

      // 計算總數
      public function allCount()
      {
          return $this->redeem->count();
      }

      // 兌換卷清單
      public function redeemList(int $page, $status = false)
      {
          $model = $this->redeem;
          if ($status == 0 || $status == 1) {
              $model = $model->where('status', $status);
          }
          if ($page == 1) {
              $page = 0;
          }
          $model = $model->offset(Redeem::PAGE_PER * $page)
              ->limit(Redeem::PAGE_PER);
          return $model->get();
      }

      // 使月者兌換卷清單
      public function getMemberRedeemList(int $memberId, int $page, int $status = 0): Collection
      {
          $model = $this->memberRedeem->where('status', $status)
              ->where('member_id', $memberId)
              ->offset(Redeem::PAGE_PER * $page)
              ->limit(Redeem::PAGE_PER);
          return $model->get();
      }

      // 更新優惠裝態
      public function updateStatus(int $id, int $status)
      {
          return $this->memberRedeem->where('id', $id)->udpate(['status' => $status]);
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
          // is_free  是否限免 0 免费视频 1vip视频 2金币视频
          // VIP限免
          // self::updateMemberRedeemUsed($code, $memberId);
          $res = self::checkRedeemVideo($discount, $videoDetail);
          if ($res == false) {
              $res = $this->memberRedeemVideoService->checkMemeberUsed($memberId, $videoId);
          }
          return $res;
      }

      // 判斷這個優惠跟此影片 是否可以觀看
      public function canRedeemVideo(array $discountAry, int $videoCate)
      {
          if (count($discountAry) > 0) {
              $result = true;
              switch ($videoCate) {
                  case 1: // VIP影片
                      $result = ! in_array(RedeemCode::FREE, $discountAry);
                      break;
                  case 2:// 付費影片
                      $result = in_array(RedeemCode::DIAMOND, $discountAry);
                      break;
              }
          }
          return $result;
      }

      // check 使用者是否有兌換影片的權限
      public function checkRedeemVideo(array $userDiscount, array $videoDetail)
      {
          // 是否限免 0 免费视频 1vip视频 2金币视频
          if (count($userDiscount) > 0) {
              foreach ($userDiscount as $discount) {
                  // 1 => 'VIP天數'
                  if ((int) $discount['redeem_category_id'] == 1 && ((int) $videoDetail['is_free'] == 0 || (int) $videoDetail['is_free'] == 2)) {
                      return $this->memberRedeemService->memberRedeemVideo($videoDetail['id'], $discount);
                  }
                  // 2 => '鑽石點數'
                  if ((int) $discount['redeem_category_id'] == 2) {
                      return $this->memberRedeemService->memberRedeemVideo($videoDetail['id'], $discount);
                  }
                  // 3 => '免費觀看次數'
                  if ((int) $discount['redeem_category_id'] == 3 && ((int) $videoDetail['is_free'] == 0)) {
                      return $this->memberRedeemService->memberRedeemVideo($videoDetail['id'], $discount);
                  }
              }
          } else {
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
          if ((self::checkUserRedeemCode($code, $memberId))){
            return ["msg"=>"您己兌換過"];
          }elseif( self::checkRedeemCode($code) ) {
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
                  return ["msg"=>"兌換成功",'vip_days'=>$redeemDetail['vip_days']];
              }
          } else {
              return ["msg"=>"己過期"];
          }
      }

      // 查看code是否己被member 使用過
      public function checkUserRedeemCode(string $code, int $memberId)
      {
        $key="redeemIsUsed:".$code.":".$memberId;
        $model = $this->memberRedeem;
        $where['member_id'] = $memberId;
        $where['redeem_code'] = $code;
        return $this->chkRedis($key, $where, $model, $this->redis); 
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
