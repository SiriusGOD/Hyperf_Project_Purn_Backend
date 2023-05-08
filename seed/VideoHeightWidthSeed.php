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
class VideoHeightWidthSeed implements BaseInterface
{
    public function up(): void
    {
        $page = 0;
        $limit = 100;
        $forever = true;
        \App\Model\Video::where('source', "0")->delete();
        while($forever) {
            $models = \App\Model\Video::whereNull('cover_height')
                ->where('cover_full', "<>", "0")
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

        \App\Model\Video::where('cover_full', "0")->update([
            'cover_height' => 0,
            'cover_witdh' => 0,
        ]);
    }

    protected function updateHeightAndWidth(\App\Model\Video $model)
    {
        $url = env("COVER_URL") . $model['cover_full'];
        $imageInfo = getimagesize($url);
        $model->cover_height = $imageInfo[1] ?? 0;
        $model->cover_witdh = $imageInfo[0] ?? 0;
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
