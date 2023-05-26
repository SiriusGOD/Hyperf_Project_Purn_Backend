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
class MemberLevelSeed implements BaseInterface
{
    public function up(): void
    {
        // vip
        $model = new \App\Model\MemberLevel();
        $model->user_id = 1;
        $model->type = 'vip';
        $model->name = 'VIP卡1天';
        $model->duration = 1;
        $model->title = '春節限時發送\r\n永久無限觀看所有視頻(含付費視頻)';
        $model->description = '春節限時發送，永久無限觀看所有視頻（含付費視頻）';
        $model->remark = '限制觀看50片';
        $model->save();

        $model = new \App\Model\MemberLevel();
        $model->user_id = 1;
        $model->type = 'vip';
        $model->name = 'VIP卡30天';
        $model->title = '春節限時發送\r\n永久無限觀看所有視頻(含付費視頻)';
        $model->description = '春節限時發送，永久無限觀看所有視頻（含付費視頻）';
        $model->remark = '';
        $model->duration = 30;
        $model->save();

        $model = new \App\Model\MemberLevel();
        $model->user_id = 1;
        $model->type = 'vip';
        $model->name = 'VIP卡90天';
        $model->title = '春節限時發送\r\n永久無限觀看所有視頻(含付費視頻)';
        $model->description = '春節限時發送，永久無限觀看所有視頻（含付費視頻）';
        $model->remark = '';
        $model->duration = 90;
        $model->save();

        $model = new \App\Model\MemberLevel();
        $model->user_id = 1;
        $model->type = 'vip';
        $model->name = 'VIP卡永久';
        $model->title = '春節限時發送\r\n永久無限觀看所有視頻(含付費視頻)';
        $model->description = '春節限時發送，永久無限觀看所有視頻（含付費視頻）';
        $model->remark = '';
        $model->duration = 3650;
        $model->save();

        // diamond
        $model = new \App\Model\MemberLevel();
        $model->user_id = 1;
        $model->type = 'diamond';
        $model->name = '鑽石卡1天';
        $model->title = '春節限時發送\r\n永久無限觀看所有視頻(含付費視頻)';
        $model->description = '春節限時發送，永久無限觀看所有視頻（含付費視頻）';
        $model->remark = '限制觀看50片';
        $model->duration = 1;
        $model->save();

        $model = new \App\Model\MemberLevel();
        $model->user_id = 1;
        $model->type = 'diamond';
        $model->name = '鑽石卡30天';
        $model->title = '春節限時發送\r\n永久無限觀看所有視頻(含付費視頻)';
        $model->description = '春節限時發送，永久無限觀看所有視頻（含付費視頻）';
        $model->remark = '';
        $model->duration = 30;
        $model->save();
    }

    public function down(): void
    {
        \App\Model\MemberLevel::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
