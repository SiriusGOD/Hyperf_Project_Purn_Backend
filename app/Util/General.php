<?php

namespace App\Util;

use App\Service\UploadService;
use Carbon\Carbon;
use Hyperf\HttpMessage\Upload\UploadedFile;

class General
{
    public static function uploadImage(UploadedFile $file, string $directory = '') : array
    {
        $extension = $file->getExtension();
        $filename = sha1(Carbon::now()->timestamp . $file->getRealPath());
        if (! file_exists(BASE_PATH . '/public/tmp')) {
            mkdir(BASE_PATH . '/public/tmp', 0755);
        }
        $imageUrl = '/tmp/' . $filename . '.' . $extension;
        $file->moveTo(BASE_PATH . '/public' . $imageUrl);
        $imageInfo = getimagesize(BASE_PATH . '/public' . $imageUrl);
        $uploadService = \Hyperf\Support\make(UploadService::class);
        $res = $uploadService->upload2Remote($filename, BASE_PATH . '/public' . $imageUrl, $directory);
        $data = [];
        $data['height'] = $imageInfo[1] ?? null;
        $data['weight'] = $imageInfo[0] ?? null;
        $data['url'] = $res['msg'];
        unlink(BASE_PATH . '/public' . $imageUrl);

        return $data;
    }
}