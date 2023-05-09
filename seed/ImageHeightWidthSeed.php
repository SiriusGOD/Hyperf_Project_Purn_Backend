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
class ImageHeightWidthSeed implements BaseInterface
{
    public function up(): void
    {
        $page = 0;
        $limit = 100;
        $forever = true;
        while($forever) {
            $models = \App\Model\Image::where('height', 0)
                ->where('sync_id', '>=', 1)
                ->offset($page * $limit)
                ->limit($limit)
                ->get();

            if ($models->isEmpty()) {
                $forever = false;
            }
            foreach ($models as $model) {
                $this->updateHeightAndWidth($model);
            }
            $page++;
        }

    }

    protected function updateHeightAndWidth(\App\Model\Image $model)
    {
        $url = env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live');
        $imageInfo = getimagesize($url . $model->url);
        $model->thumbnail_height = $imageInfo[1] ?? 0;
        $model->thumbnail_weight = $imageInfo[0] ?? 0;
        $model->height = $imageInfo[1] ?? 0;
        $model->weight = $imageInfo[0] ?? 0;
        $model->save();
    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return false;
    }
}
