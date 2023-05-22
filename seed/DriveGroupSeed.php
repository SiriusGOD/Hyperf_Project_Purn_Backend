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
class DriveGroupSeed implements BaseInterface
{
    public function up(): void
    {
        // 車群類別
        $model = new \App\Model\DriveClass();
        $model->name = '官方TG';
        $model->user_id = 1;
        $model->description = '';
        $model->save();

        $model = new \App\Model\DriveClass();
        $model->name = '片片交流群';
        $model->user_id = 1;
        $model->description = '';
        $model->save();

        // 車群
        $model = new \App\Model\DriveGroup();
        $model->name = 'Pornterest 官方頻道';
        $model->user_id = 1;
        $model->img = '/upload/icons/20230522/2023052218010760108.png';
        $model->url = 'https://t.me/Pornterest_Official';
        $model->save();

        $model = new \App\Model\DriveGroup();
        $model->name = 'Pornterest TG交流群';
        $model->user_id = 1;
        $model->img = '/upload/icons/20230522/2023052218012929149.png';
        $model->url = 'https://t.me/PornterestOfficial';
        $model->save();

        $model = new \App\Model\DriveGroup();
        $model->name = 'Pornterest 推特群';
        $model->user_id = 1;
        $model->img = '/upload/icons/20230522/2023052218014851032.png';
        $model->url = 'https://twitter.com/pornterest69';
        $model->save();

        // 關聯
        $model = new \App\Model\DriveGroupHasClass();
        $model->drive_class_id = 1;
        $model->drive_group_id = 1;
        $model->save();

        $model = new \App\Model\DriveGroupHasClass();
        $model->drive_class_id = 2;
        $model->drive_group_id = 2;
        $model->save();

        $model = new \App\Model\DriveGroupHasClass();
        $model->drive_class_id = 2;
        $model->drive_group_id = 3;
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
