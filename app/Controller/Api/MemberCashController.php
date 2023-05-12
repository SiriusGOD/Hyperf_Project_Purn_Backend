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
namespace App\Controller\Api;

use App\Constants\WithdrawCode;
use App\Controller\AbstractController;
use App\Service\ActorClassificationService;
use App\Service\MemberCashAccountService;
use App\Service\WithdrawService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;
use App\Util\Check;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
class MemberCashController extends AbstractController
{
  //create my account 
  //del account
  //
    #[RequestMapping(methods: ['POST'], path: 'createAccount')]
    public function createAccount(RequestInterface $request, MemberCashAccountService $memberCaseAccountService)
    {
        $data = $request->all();
        $data['member_id'] = auth('jwt')->user()->getId();
        $result = $memberCaseAccountService->store($data);
        return $this->success(['models' => $result]);
    }

    /*
     * 我的收益明細
     * */
    #[RequestMapping(methods: ['POST'], path: 'myincomeList')]
    public function myincomeList(RequestInterface $request, WithdrawService $withdrawService)
    {
        $page = $request->input('page',0);
        $limit= $request->input('limit',10);
        $member_id = auth('jwt')->user()->getId();
        $result = $withdrawService->incomeList($page, $limit ,$member_id);
        return $this->success(['models' => $result]);
    }
    /*
     * 提領記錄
     * */
    #[RequestMapping(methods: ['POST'], path: 'withdrawList')]
    public function withdrawList(RequestInterface $request, WithdrawService $withdrawService)
    {
        $page = $request->input('page',0);
        $limit= $request->input('limit',10);
        $member_id = auth('jwt')->user()->getId();
        $result = $withdrawService->myWithdrawList($page, $limit ,$member_id);
        return $this->success(['models' => $result]);
    }

    #[RequestMapping(methods: ['POST'], path: 'getListByClassification')]
    public function getListByClassification(RequestInterface $request, ActorClassificationService $service)
    {
        $type_id = (int) $request->input('type_id', 0);
        $result = $service->getListByClassification($type_id);
        return $this->success(['models' => $result]);
    }
    /**
     *  提现 视频 、推广
     */
    #[RequestMapping(methods: ['POST'], path: 'withdraw')]
    public function withdraw(RequestInterface $request,WithdrawService $withdrawService)
    {
        /** @var MemberModel $member */
        $data = $request->all();
        $data['member_id'] = auth('jwt')->user()->getId();
        if (false) {
           // if ($member->expired_at < time()) {
                return $this->error(trans('api.member_cash_control.membership_expires'), 422);
            //} elseif ($member->vip_level < MemberModel::VIP_LEVEL_YEAR) {
                return $this->error(trans('api.member_cash_control.buy_membership_expires'), 422);
            //}
        }
        $requires  = ['name','account','channel','account_name','withdraw_type','withdraw_amount'] ; 
        $inputs = $request->all();
        $check=Check::require($inputs , $requires);
        if($check){
          return $this->error( trans('default.withdraw.empty_error', ["key" => $check]), WithdrawCode::EMPTY_ERROR);  
        }
        $withdraw_type = 1;//收款方式 银行卡
        $withdraw_from = 3;  //2代理 3 视频收益
        $withdraw_amount= $request->input("withdraw_amount"); 
        $rate = 1;
        $withdraw_amount_money= 0;

        $insert_data = [
                'id'            => null,
                'member_id'     => auth('jwt')->user()->getId(),
                'uuid'          => auth('jwt')->user()->getId(),
                'type'          => $request->input("withdraw_type"),
                'account_name'  => $request->input("account_name"),//开户行
                'account'       => $request->input("account") ,//账号
                'name'          => $request->input("name"),//开户姓名
                'amount'        => $withdraw_amount,//扣费到账
                'coins'         => $withdraw_amount,//扣费到账
                'trueto_amount' => 0,//实际到账金额
                'status'        => 0,
                'descp'         => "申请:{$withdraw_amount} 费率:{$rate} 到账:{$withdraw_amount_money}",
                'coins'         => $request->input("withdraw_amount"),//实际提现
                'withdraw_type' => $withdraw_type,
                'withdraw_from' => $withdraw_from,
                'ip'            => '127.0.0.1',
                'cash_id'       => '127.0.0.1',
                'payed_at'      => date("Y-m-d H:i:s"),
                'channel'       => "Y-m-d H:i:s",
                'third_id'      => "Y-m-d H:i:s",
                'order_desc'    => "Y-m-d H:i:s",
                'address'       => '127.0.0.1' 
                //'address'       => \UserWithdrawModel::convertIPToAddress(USER_IP)
            ];
        $withdrawService->store($insert_data);
        //$redisKey = UserWithdrawModel::REDIS_USER_WITH_DRAW . "{$member->uid}";
        //$res = redis()->get($redisKey);
        //if ($res) {
        //    if (env('APP_ENV')== 'product') {
        //        return $this->error('10分钟内只能发起一次提现请求~');
        //    }
        //}

        /*if (!array_key_exists($withdraw_from, UserWithdrawModel::DRAW_TYPE)) {
            return $this->errorJson('无效提现类型~', 422);
        }*/
        //if ($withdraw_amount <= 0 || $withdraw_amount % 100 != 0) {
        //    return $this->errorJson('提现额度必须是100整数倍~', 422);
        //}
        //$result = null;
        ///** @var MemberModel $member */
        //$member = MemberModel::onWriteConnection()->find($member->aff);
        //if (is_null($member)) {
        //    return $this->errorJson('用户信息走丢了');
        //}

        //if ($withdraw_amount > $member->coins) {
        //    return $this->errorJson('提现账户余额不足~', 422);
        //}
        //$rate = UserWithdrawModel::USER_WITHDRAW_CHANNEL_RATE;
        //$withdraw_amount_money = $withdraw_amount * (1 - $rate);

        //if ($withdraw_amount_money < 100) {
        //    return $this->errorJson('到账金额须大等于100元可申请提现~', 422);
        //}
        //try {
        //    \DB::beginTransaction();
        //    // 提现记录
       //    if (MemberModel::where([
        //        ['uid', '=', $member->uid],
        //        ['coins', '>=', $withdraw_amount]

        //    ])->decrement('coins', $withdraw_amount)) {
        //        UserWithdrawModel::create($insert_data);
        //    } else {
        //        throw new \Yaf\Exception('申请提现失败.~', 422);
        //    }
        //    \DB::commit();
        //} catch (Exception $exception) {
        //    \DB::rollBack();
        //    error_log("withdraw:{$exception->getMessage()}");
        //    throw new \Yaf\Exception('申请失败，请稍后再试', 422);
        //}
        //MemberModel::clearFor($this->member);
        //redis()->setex($redisKey, 600, 1);
        return $this->success(['success' => true, 'msg' => trans('api.member_cash_control.success')]);
    }
}
