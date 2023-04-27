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
namespace App\Util;

class Calc
{
    public static function imgSize(string $url)
    {
        // 讀取圖片數據
        $image_data = file_get_contents($url);
        // 將圖片數據存儲到臨時文件中
        $temp_file = tempnam(sys_get_temp_dir(), 'image');
        file_put_contents($temp_file, $image_data);
        // 獲取圖片大小信息
        $image_size = getimagesize($temp_file);
        // 獲取圖片寬度和高度
        $data['width'] = $image_size[0];
        $data['height'] = $image_size[1];
        // 刪除臨時文件
        unlink($temp_file);
        return $data;
    }
}
