<?php

namespace App\Util;

class General
{
    public static function getImageUrl(string $url)
    {
        // 取得網址前綴
        $urlArr = parse_url($url);
        $port = $urlArr['port'] ?? '80';
        return $urlArr['scheme'] . '://' . $urlArr['host'] . ':' . $port;
    }
}