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

class VideoCode
{
  //type 1 长横幅 2 短 竖图
  public const TYPE = [0=> "未設定", 1 => "长横幅", 2 => "短竖图"  ];
  //category 类型 0 mv 1 av 2 ai 3 动漫 4  live 5 gay
  
  public const CATEGORY = [0=> "未設定",1 => "mv", 2 => "av", 3=>'动漫', 4 =>'live', 5=>"gay"];

  //is_free  是否限免 0 免费视频 1vip视频 2金币视频
  public const IS_FREE = [0=> "免费视频",  1=>"vip视频", 2=>"金币视频"];

  //is_hide  0显示1隐藏
  public const IS_HIDE = [0=> "显示", 1 => "隐藏"];

}
