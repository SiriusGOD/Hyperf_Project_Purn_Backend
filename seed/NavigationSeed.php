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
class NavigationSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\Navigation();
        $model->user_id = 1;
        $model->name = '大家都在看';
        $model->hot_order = 1;
        $model->save();

        $model = new \App\Model\Navigation();
        $model->user_id = 1;
        $model->name = '專屬推薦';
        $model->hot_order = 2;
        $model->save();

        $model = new \App\Model\Navigation();
        $model->user_id = 1;
        $model->name = '最新推薦';
        $model->hot_order = 3;
        $model->save();
    }

    public function down(): void
    {
        \App\Model\Navigation::truncate();
    }

    public function base(): bool
    {
        return true;
    }
}
