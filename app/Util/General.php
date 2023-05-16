<?php

namespace App\Util;

use App\Service\UploadService;
use Carbon\Carbon;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Intervention\Image\ImageManager;

class General
{
    public const UPLOAD_LIMIT = 2500000;
    public static function uploadImage(UploadedFile $file, string $directory = '') : array
    {
        $filename = sha1(Carbon::now()->timestamp . $file->getRealPath());
        if (! file_exists(BASE_PATH . '/public/tmp')) {
            mkdir(BASE_PATH . '/public/tmp', 0755);
        }
        $imageUrl = '/tmp/' . $filename . '.' . 'jpg';
        $path = BASE_PATH . '/public' . $imageUrl;
        $file->moveTo($path);

        $manager = new ImageManager(['driver' => 'gd']);
        $manager = $manager->make($path);
        if ($manager->filesize() >= self::UPLOAD_LIMIT) {
            $manager->resize(intval($manager->width() * 0.5), intval($manager->height() * 0.5))->save($path, 50);
        }


        $imageInfo = getimagesize($path);
        $uploadService = \Hyperf\Support\make(UploadService::class);
        $res = $uploadService->upload2Remote($filename, $path, $directory);
        $data = [];
        $data['height'] = $imageInfo[1] ?? null;
        $data['weight'] = $imageInfo[0] ?? null;
        $data['url'] = $res['msg'];
        $data['code'] = $res['code'];
        unlink(BASE_PATH . '/public' . $imageUrl);

        return $data;
    }
}