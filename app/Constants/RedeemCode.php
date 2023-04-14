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

class RedeemCode
{
    public const VIP = 1;
    public const DIAMOND = 2;
    public const FREE = 3;
    // type 1 长横幅 2 短 竖图
    public const CATEGORY = [self::VIP => 'VIP天數', self::DIAMOND => '鑽石點數', self::FREE => '免費觀看次數'];
}
