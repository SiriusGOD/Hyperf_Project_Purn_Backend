<?php

declare(strict_types=1);

use App\Service\UploadService;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class UploadImageSeed implements BaseInterface
{
    public function up(): void
    {
        $models = \App\Model\Advertisement::all();

        foreach ($models as $model) {
            $fullPath = BASE_PATH . '/public' . $model->image_url;
            var_dump($fullPath);
            if(!is_file($fullPath)) {
                continue;
            }
            $uploadService = \Hyperf\Support\make(UploadService::class);
            $pathArr = explode('/', $model->image_url);
            $name = $pathArr[count($pathArr) - 1];
            $res = $uploadService->upload2Remote($name, BASE_PATH . '/public' . $model->image_url, 'advertisement');
            $model->image_url = $res['msg'];
            $model->save();
        }

        $models = \App\Model\CustomerServiceDetail::where('image_url', '<>', '')->get();

        foreach ($models as $model) {
            $fullPath = BASE_PATH . '/public' . $model->image_url;
            if(!is_file($fullPath)) {
                continue;
            }
            $uploadService = \Hyperf\Support\make(UploadService::class);
            $pathArr = explode('/', $model->image_url);
            $name = $pathArr[count($pathArr) - 1];
            $res = $uploadService->upload2Remote($name, BASE_PATH . '/public' . $model->image_url, 'customer_service');
            $model->image_url = $res['msg'];
            $model->save();
        }

        $models = \App\Model\CustomerServiceCover::all();

        foreach ($models as $model) {
            $fullPath = BASE_PATH . '/public' . $model->url;
            if(!is_file($fullPath)) {
                continue;
            }
            $uploadService = \Hyperf\Support\make(UploadService::class);
            $pathArr = explode('/', $model->url);
            $name = $pathArr[count($pathArr) - 1];
            $res = $uploadService->upload2Remote($name, BASE_PATH . '/public' . $model->url, 'customer_service');
            $model->url = $res['msg'];
            $model->save();
        }

        $models = \App\Model\Actor::all();

        foreach ($models as $model) {
            $fullPath = BASE_PATH . '/public' . $model->avatar;
            if(!is_file($fullPath)) {
                continue;
            }
            $uploadService = \Hyperf\Support\make(UploadService::class);
            $pathArr = explode('/', $model->avatar);
            $name = $pathArr[count($pathArr) - 1];
            $res = $uploadService->upload2Remote($name, BASE_PATH . '/public' . $model->avatar, 'actor');
            $model->avatar = $res['msg'];
            $model->save();
        }
    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return false;
    }
}
