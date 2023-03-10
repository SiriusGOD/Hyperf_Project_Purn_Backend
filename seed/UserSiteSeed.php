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
class UserSiteSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\UserSite();
        $model->user_id = 1;
        $model->site_id = 1;
        $model->save();

        $model = new \App\Model\UserSite();
        $model->user_id = 1;
        $model->site_id = 2;
        $model->save();
    }

    public function down(): void
    {
        \App\Model\UserSite::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
