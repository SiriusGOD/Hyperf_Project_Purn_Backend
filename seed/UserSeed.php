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
class UserSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\User();
        $model->name = 'admin';
        $model->password = Hash::make('quH25df15Ed');
        $model->sex = 1;
        $model->age = 20;
        $model->avatar = '';
        $model->email = 'admin@admin.com';
        $model->phone = '012345678';
        $model->status = 1;
        $model->role_id = 1;
        $model->save();

        $model = new \App\Model\User();
        $model->name = 'test';
        $model->password = Hash::make('quH25df15Ed');
        $model->sex = 1;
        $model->age = 20;
        $model->avatar = '';
        $model->email = 'test@test.com';
        $model->phone = '098765432';
        $model->status = 1;
        $model->role_id = 2;
        $model->save();
    }

    public function down(): void
    {
        \App\Model\User::truncate();
    }

    public function base(): bool
    {
        return true;
    }
}
