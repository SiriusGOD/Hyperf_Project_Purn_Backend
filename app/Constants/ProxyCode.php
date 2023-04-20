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

class ProxyCode
{
    public const LEVEL_1 = 1; // 荣耀黄金

    public const LEVEL_2 = 2; // 尊贵铂金

    public const LEVEL_3 = 3; // 永恒钻石

    public const LEVEL_4 = 4; // 至尊星耀

    public const COIN_RATE = [
      1=>   ['money'=> 1000  , 'rate'=> 0.1 ],
      2=>   ['money'=> 2000  , 'rate'=> 0.12],
      3=>   ['money'=> 5000  , 'rate'=> 0.14],
      4=>   ['money'=> 10000 , 'rate'=> 0.16],
      5=>   ['money'=> 20000 , 'rate'=> 0.18],
      7=>   ['money'=> 40000 , 'rate'=> 0.20],
      8=>   ['money'=> 70000 , 'rate'=> 0.23],
      9=>   ['money'=> 99999 , 'rate'=> 0.26],
      10=>  ['money'=> 100000, 'rate'=> 0.30]
    ];
  
    public const LEVEL = [
        self::LEVEL_4 => [
            'rate' => 0.1,
            'name' => '铂金级',
            'vip' => 100,
            'level_rule' => [
                'pre_level' => 2, // 上一级
                'number' => 2, // 限额
            ],
        ], // 累计直推150付费会员+2直属永恒钻石以上代理
        self::LEVEL_3 => [
            'rate' => 0.15,
            'name' => '黄金级',
            'vip' => 50,
            'level_rule' => [
                'pre_level' => 2, // 上一级
                'number' => 2, // 限额
            ],
        ], // 累计直推50付费会员+2直属尊贵铂金以上代理
        self::LEVEL_2 => [
            'rate' => 0.25,
            'name' => '白银级',
            'vip' => 20,
            'level_rule' => [
                'pre_level' => 1, // 上一级
                'number' => 2, // 限额
            ],
        ], // 累计直推5付费会员+2直属荣耀黄金以上代理
        self::LEVEL_1 => [
            'rate' => 1,
            'name' => '青铜级',
            'vip' => 5,
            'level_rule' => [
                'pre_level' => 0, // 上一级
                'number' => 0, // 限额
            ],
        ], // 累计直推1付费会员
    ];
}
