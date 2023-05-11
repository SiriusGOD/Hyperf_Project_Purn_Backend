<?php

namespace App\Util;

class General
{
    public static function getImageUrl(string $url)
    {
        if (!empty(\Hyperf\Support\env('TEST_IMG_URL'))) {
            return \Hyperf\Support\env('TEST_IMG_URL');
        }
        // 取得網址前綴
        $urlArr = parse_url($url);
        $port = $urlArr['port'] ?? '80';
        return $urlArr['scheme'] . '://' . $urlArr['host'] . ':' . $port;
    }
}