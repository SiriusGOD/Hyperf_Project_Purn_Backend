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
namespace App\Constants;

class WithdrawCode
{
    public const PAGE_PER =10;
    public const DEFAULT = 0;
    public const SUCCESS = 1;
    public const FAILD = 2;
    //查無記錄
    public const NO_DATA = 11001;
    //提现拒绝操作成功
    public const REJECT_WITHDRAW = 11002;
    //提现拒绝操作成功
    public const PASS_WITHDRAW = 11003;
    //AJAX錯誤
    public const AJAX_ERROR = 11004;
    //操作失败，联系管理员~
    public const OPERATION_ERROR = 11005;
    //USDT 操作成功~
    public const USDT_SUCCESS = 11006;
  
    //缺少參數~
    public const EMPTY_ERROR = 11007;
  
    //你餘額不足~
    public const NO_MONEY = 11008;
    
    //請輸入數字
    public const NOT_NUMBER_EMPTY_ERROR = 11009;

    // 提现状态 0:审核中;1:已完成;2:未通过
    public const STATUS = [self::DEFAULT => '审核中', self::SUCCESS => '已完成', self::FAILD => '未通过'];

    // 类型，1表示支付宝，2表示微信，0表示银行卡
    public const TYPE = [1=>'Paypel' ,2 => '银行卡'];

    //提现收款方式1银行卡2
    public const WITHDRAW_TYPE = [ 1=>'Paypel' ,2 => '银行卡'];

    //提现来源1货币2代理
    public const WITHDRAW_FROM = [ 1 => '货币', 2 => '代理'];

    const STATUS_REFUSE = 5; // 提现拒绝
    const STATUS_SUCCESS = 1; // 提现审核
    const STATUS_POST = 2; // 提现完成
    const STATUS_FREE = 3; // 已解冻
    const STATUS_EXAMINE = 0; // 审核中
    const STATUS_FAIL = 4; //提现失败

    const STATUS_TEXT = [
        self::STATUS_EXAMINE => '审核中',
        self::STATUS_SUCCESS => '待请款',
        self::STATUS_POST    => '已打款',
        self::STATUS_FREE    => '审核中',
        self::STATUS_FAIL    => '提现失败',
        self::STATUS_REFUSE  => '提现失败',
    ];

    const REDIS_USER_WITH_DRAW = 'draw:'; // 用户提现防并发key

    const DRAW_TYPE_PROXY = 0;//代理收益
    const DRAW_TYPE_MV = 1;//视频裸聊收益
    const DRAW_TYPE = [
        self::DRAW_TYPE_PROXY => '代理推广', // Agency promotion
        self::DRAW_TYPE_MV    => '视频｜裸聊|约炮', // Video｜Naked Chat
    ];

    const USER_WITHDRAW_CHANNEL_RATE = 0.05;  //提现比例通道费比例  10%
    const USER_MIN_BANLANCE_RATE = 0.70; // 视频 裸聊 打赏最低收益到账比例、到账比例

}
