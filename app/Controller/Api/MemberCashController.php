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
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Service\ActorClassificationService;
use App\Service\MemberCashAccountService;
use App\Service\WithdrawService;
use App\Service\MemberService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;
use App\Util\Check;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
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
    public function withdraw(RequestInterface $request,WithdrawService $withdrawService, MemberService $memberService)
    {
        /** @var MemberModel $member */
        $data = $request->all();
        $member_id = auth('jwt')->user()->getId();
        $data['member_id'] = $member_id;
        $requires  = ['name','account','bank_type','withdraw_amount',"password"] ; 
        $check = Check::require($request->all() , $requires);
        if( !is_numeric($request->input('withdraw_amount')) ){
          return $this->error( trans('default.withdraw.is_not_number_error'), WithdrawCode::NOT_NUMBER_EMPTY_ERROR);  
        }
        if($check){
          $title = trans("default.withdraw.$check");
          return $this->error( trans('default.withdraw.empty_error', ["key" => $title]), WithdrawCode::EMPTY_ERROR);  
        }
        $withdraw_type = $request->input('bank_type');//收款方式 1:paypel, 2:银行卡
        $amount = $request->input('withdraw_amount');//收款方式 1:paypel, 2:银行卡
        if((int)$amount > auth('jwt')->user()->coins){
            return $this->error(trans('default.withdraw.no_money'),WithdrawCode::NO_MONEY);
        } 
        $check = $memberService->checkPassword($request->input('password', ''), auth('jwt')->user()->password);
        if (! $check ) {
            return $this->error(trans('validation.password_error'), 401);
        }
        $withdraw_from = 3;  //2代理 3 视频收益
        $withdraw_amount= $request->input("withdraw_amount"); 
        $rate = 1;
        $withdraw_amount_money= 0;
        $account_name = WithdrawCode::WITHDRAW_TYPE[$request->input("bank_type" ,1)];
        $insert_data = [
                'id'            => null,
                'member_id'     => auth('jwt')->user()->getId(),
                'uuid'          => auth('jwt')->user()->getId(),
                'type'          => $request->input("bank_type"),
                'account_name'  => $account_name,//开户行
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
        return $this->success(['success' => true, 'msg' => trans('api.member_cash_control.success')]);
    }
}
