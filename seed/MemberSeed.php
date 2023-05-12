<?php

declare(strict_types=1);
use Hyperf\Utils\Str;
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class MemberSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\Member();
        $model->name = 'admin';
        $model->password = password_hash('quH25df15Ed', PASSWORD_DEFAULT);
        $model->sex = 1;
        $model->age = 20;
        $model->avatar = '';
        $model->email = 'admin@admin.com';
        $model->phone = '012345678';
        $model->status = 1;
        $model->member_level_status =0;
        $model->aff= Str::random(5);
        $model->save();

        $model = new \App\Model\Member();
        $model->name = 'test';
        $model->password = password_hash('quH25df15Ed', PASSWORD_DEFAULT);
        $model->sex = 1;
        $model->age = 20;
        $model->avatar = '';
        $model->email = 'test@test.com';
        $model->phone = '098765432';
        $model->status = 1;
        $model->member_level_status =0;
        $model->aff= Str::random(5);
        $model->save();

        for($i=1 ; $i<=20 ;$i++)
        {
          $model = new \App\Model\Member();
          $model->name = 'test'.$i;
          $model->member_level_status =0;
          $model->password = password_hash('quH25df15Ed', PASSWORD_DEFAULT);
          $model->sex = 1;
          $model->age = 20;
          $model->avatar = '';
          $model->email = $i.'test@test.com';
          $model->phone = '098765432'.$i;
          $model->status = 1;
          $model->aff= Str::random(5);
          $model->save();
        }
    }

    public function down(): void
    {
        \App\Model\Member::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
