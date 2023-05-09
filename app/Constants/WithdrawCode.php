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

    // 提现状态 0:审核中;1:已完成;2:未通过
    public const STATUS = [self::DEFAULT => '审核中', self::SUCCESS => '已完成', self::FAILD => '未通过'];

    // 类型，1表示支付宝，2表示微信，0表示银行卡
    public const TYPE = [0 => '银行卡', 1 => '支付宝', 2 => '微信'];

    //提现收款方式1银行卡2
    public const WITHDRAW_TYPE = [ 1 => '银行卡', 2 => '微信'];

    //提现来源1货币2代理
    public const WITHDRAW_FROM = [ 1 => '货币', 2 => '代理'];

}
