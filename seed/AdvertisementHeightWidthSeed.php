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
class AdvertisementHeightWidthSeed implements BaseInterface
{
    public function up(): void
    {
        $page = 0;
        $limit = 100;
        $forever = true;
        while($forever) {
            $imageGroups = \App\Model\Advertisement::where('height', 0)
                ->whereNotNull('image_url')
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

    protected function updateHeightAndWidth(\App\Model\Advertisement $model)
    {
        $imageInfo = getimagesize(BASE_PATH . '/public'. $model->image_url);
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
