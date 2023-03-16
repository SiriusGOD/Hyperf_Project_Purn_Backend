<?php

declare(strict_types=1);

use HyperfExt\Hashing\Hash;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class TagSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\Tag();
        $model->name = 'test';
        $model->user_id = 1;
        $model->save();

        $model = new \App\Model\Tag();
        $model->name = 'test2';
        $model->user_id = 1;
        $model->save();

    }

    public function down(): void
    {
        \App\Model\Tag::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
