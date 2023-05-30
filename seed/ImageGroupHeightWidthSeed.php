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
class ImageGroupHeightWidthSeed implements BaseInterface
{
    public function up(): void
    {
        $page = 0;
        $limit = 100;
        $forever = true;
        while($forever) {
            $imageGroups = \App\Model\ImageGroup::where('height', 0)
                ->where('sync_id', '>=', 1)
                ->offset($page * $limit)
                ->limit($limit)
                ->get();

            if (count($imageGroups) == 0) {
                $forever = false;
            }
            foreach ($imageGroups as $imageGroup) {
                $this->updateHeightAndWidth($imageGroup);
            }
            $page++;
        }

    }

    protected function updateHeightAndWidth(\App\Model\ImageGroup $model)
    {
        $url = env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live');
        $imageInfo = getimagesize($url . $model->thumbnail);
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
