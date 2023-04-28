<?php

namespace App\Util;

class General
{
    public static function getImageUrl(string $url)
    {
        // 取得網址前綴
        $urlArr = parse_url($url);
        $port = $urlArr['port'] ?? '80';
        if (! empty(env('TEST_IMG_URL'))) {
            $host = env('TEST_IMG_URL');
        } else {
            $host = $urlArr['scheme'] . '://' . $urlArr['host'] . ':' . $port;
        }

        return $host;
    }
}