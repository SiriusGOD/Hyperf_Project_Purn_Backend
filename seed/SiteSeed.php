<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @see     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class SiteSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\Site();
        $model->name = 'test';
        $model->url = 'www.google.com';
        $model->save();

        $model = new \App\Model\Site();
        $model->name = 'test2';
        $model->url = 'www.google2.com';
        $model->save();

        $model = new \App\Model\Site();
        $model->name = 'test3';
        $model->url = 'www.google3.com';
        $model->deleted_at = \Carbon\Carbon::now()->toDateTimeString();
        $model->save();
    }

    public function down(): void
    {
        \App\Model\Site::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
