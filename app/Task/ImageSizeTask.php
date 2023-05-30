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
namespace App\Task;

use App\Model\ImageGroup;
use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(name: 'ImageSizeTask', rule: '10 * * * *', callback: 'execute', memo: '圖片大小計算任務')]
class ImageSizeTask
{
    public function __construct()
    {
    }

    public function execute()
    {
        $page = 0;
        $limit = 100;
        $models = \App\Model\Image::where('height', 0)
            ->where('sync_id', '>=', 1)
            ->offset($page * $limit)
            ->limit($limit)
            ->get();

        foreach ($models as $model) {
            $this->updateHeightAndWidth($model);
        }
    }

    protected function updateHeightAndWidth(ImageGroup $model)
    {
        $url = env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live');
        $imageInfo = getimagesize($url . $model->url);
        $model->thumbnail_height = $imageInfo[1] ?? 0;
        $model->thumbnail_weight = $imageInfo[0] ?? 0;
        $model->height = $imageInfo[1] ?? 0;
        $model->weight = $imageInfo[0] ?? 0;
        $model->save();
    }
}
