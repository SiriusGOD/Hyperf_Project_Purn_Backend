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
use App\Constants\WithdrawCode;
use App\Model\MemberWithdraw;
use App\Model\Order;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
//提現
class WithdrawService extends BaseService
{
    protected Redis $redis;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('withdraw');
    }

    //提現取得
    public function fetch(int $id){
      return $this->get(MemberWithdraw::class , $id);
    }

    //提現數量
    public function count(int $status){
      return MemberWithdraw::where('status',$status)->count();
    }
  
    //我的 收益明細
    public function incomeList(int $page, int $limit, int $member_id){
      return MemberWithdraw::where('member_id',$member_id)
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)->get();
    }
    //會員提現列表
    public function myWithdrawList(int $page, int $limit, int $member_id){
    //statusColor
      $sqlType = "CASE `type`
           WHEN 1 THEN '".trans("default.withdraw.paypel")."'
           WHEN 2 THEN '".trans("default.withdraw.bankcard")."'
           ELSE ''
       END AS `bank_type`";

      $payDate = " DATE_FORMAT(payed_at, '%Y-%m-%d') AS formatted_date";
      $case = "CASE `status`
           WHEN 0 THEN '".trans("default.withdraw.default")."'
           WHEN 1 THEN '".trans("default.withdraw.pass")."'
           WHEN 2 THEN '".trans("default.withdraw.reject")."'
           ELSE ''
       END AS `status_msg`";

      $statusCase = "`status` AS `status_color`";

      $select =[DB::raw($sqlType),"amount",DB::raw($payDate),"account",DB::raw($statusCase) , DB::raw($case)];

      return MemberWithdraw::select($select)->where('member_id',$member_id)
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)->get();
    }

    //提現列表
    public function withdrawList(int $page, int $limit, int $status){
      return MemberWithdraw::where('status',$status)
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)->get();
    }

    //儲存提現訂單
    public function store(array $data)
    {
        $model = MemberWithdraw::findOrNew($data['id']);
        Db::beginTransaction();
        try {
            foreach ($data as $key => $val) {
                $model->{$key} = $val;
            }
            $model->save();
            Db::commit();
        } catch (\Throwable $ex) {
            $this->logger->error($ex->getMessage(), $data);
            Db::rollBack();
            return false;
        }
        return $model;
    }

    //更新提現訂單設定 
    public function setWithDraw(array $inputData){
        $id = $inputData['id'];
        $flag = $inputData['flag'];
        $admin_name = auth('session')->user()->name;
        $withdraw = MemberWithdraw::where('id', $id)->where('status', 0)->first();
        if (is_null($withdraw)) {
          return [trans('default.withdraw.no_data'), WithdrawCode::NO_DATA];
        }
        if ('usdt' == $flag) {
            $update = [
                'status'     => WithdrawCode::STATUS_POST,
                'channel'    => 'usdt-' . $admin_name,
                'cash_id'    => 'usdt-' . md5($admin_name),
                'order_desc' => "[$admin_name] usdt处理",
                'updated_at' => date("Y-m-d H:i:s"),
            ];
            $isOk = $withdraw->update($update);
            if ($isOk) {
                return [trans('default.withdraw.withdraw_usdt_success'), WithdrawCode::USDT_SUCCESS];
          //      return $this->ajaxSuccess('USDT 操作成功');
            }
      
        } elseif ('refuse' == $flag) {
            //审核不通过 退回申请余额
            $update = [
                'status'     => WithdrawCode::STATUS_REFUSE,
                'order_desc' => "[$admin_name] 拒绝处理",
                'updated_at' => date("Y-m-d H:i:s"),
            ];
            $update['id'] = $id ;
            $isOk = $this->modelStore($withdraw, $update);
            if ($isOk) {
                if($withdraw->withdraw_from == WithdrawCode::DRAW_TYPE_PROXY){//代理
                    //Member::where(['id' => $withdraw->member_id])->increment('tui_coins', $withdraw->coins);
                }elseif($withdraw->withdraw_from == WithdrawCode::DRAW_TYPE_MV){//视频 ｜ 裸聊
                    //Member::where(['id' => $withdraw->member_id])->increment('g_coins', $withdraw->coins);
                }
                //return $this->ajaxSuccess('提现拒绝操作成功');
                return [trans('default.withdraw.withdraw_reject') , WithdrawCode::REJECT_WITHDRAW];
            }
        } elseif ('pass' == $flag) {
            /** @var MemberModel $memberinfo */

            //发起请求
            $data = array(
                "app_id"      => $withdraw->id,
                "app_name"    => env("SYSTEM_ID"),
                "app_type"    => 'app',
                "username"    => trim($withdraw->name),
                "type"        => 'bankcard',
                "card_number" => trim($withdraw->account),
                'bankcode'    => '',
                "amount"      => $withdraw->amount,
                "aff"         => $withdraw->member->aff,
                "phone"       => "",
                "notify_url"  => env("SYSTEM_NOTIFY_WITHDRAW_URL"),
            );
            ksort($data);
            $str = "";
            foreach ($data as $row) {
                $str .= $row;
            }
            $user_withdraw_key = env('pay.user_withraw_key');
            $user_withdraw_url = env('pay.user_withraw_url');
            env("APP_ENV") != 'product' && $user_withdraw_url = '';
            $data['sign'] = md5($str . $user_withdraw_key);
            errLog('proxy:'.var_export($data,true));
            $re = di(\App\Service\UploadService::class)->deleteMp4($user_withdraw_url, $data);
            errLog('proxy-result:' . var_export([$data, $re], true));
            $re = json_decode($re, true);
            if (isset($re['success']) && $re['success'] == true && $re['data']['code'] == 200) {
                $data = [
                    'updated_at' => time(),
                    'status'     => WithdrawCode::STATUS_SUCCESS,
                    'channel'    => $re['data']['channel'],
                    'cash_id'    => $re['data']['order_id'],
                    'descp'      => "[$admin_name] 批准审核"
                ];
                MemberWithdraw::where(['id' => $withdraw->id])->update($data);
                //return $this->ajaxSuccess('请款操作成功');
                return [ trans('default.withdraw.withdraw_pass_success'), WithdrawCode::PASS_WITHDRAW];
            }
            return [ $re['errors'][0]['message'], WithdrawCode::AJAX_ERROR];
            //return $this->ajaxError($re['errors'][0]['message']);
        }
        return [ trans('default.withdraw.withdraw_faild'), WithdrawCode::OPERATION_ERROR];
        //$this->ajaxError('操作失败，联系管理员~'); 

    }
}
