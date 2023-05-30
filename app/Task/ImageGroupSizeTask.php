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

#[Crontab(name: 'ImageGroupSizeTask', rule: '1 * * * *', callback: 'execute', memo: '套圖大小計算任務')]
class ImageGroupSizeTask
{
    public function __construct()
    {
    }

    public function execute()
    {
        $page = 0;
        $limit = 100;
        $imageGroups = \App\Model\ImageGroup::where('height', 0)
            ->where('sync_id', '>=', 1)
            ->offset($page * $limit)
            ->limit($limit)
            ->get();

        foreach ($imageGroups as $imageGroup) {
            $this->updateHeightAndWidth($imageGroup);
        }
    }

    protected function updateHeightAndWidth(ImageGroup $model)
    {
        $url = env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live');
        $imageInfo = getimagesize($url . $model->thumbnail);
        $model->height = $imageInfo[1] ?? 0;
        $model->weight = $imageInfo[0] ?? 0;
        $model->save();
    }
}
