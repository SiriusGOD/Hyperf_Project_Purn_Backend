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
class ActorClassificationSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\ActorClassification();
        $model->name = '麻豆女优';
        $model->user_id = 1;
        $model->sort = 1;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '91制片女优';
        $model->user_id = 1;
        $model->sort = 2;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '果冻女优';
        $model->user_id = 1;
        $model->sort = 3;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '天美 | 蜜桃 |星空女优';
        $model->user_id = 1;
        $model->sort = 4;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '糖心 | 杏吧 | 性世界女优';
        $model->user_id = 1;
        $model->sort = 5;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '探花女优';
        $model->user_id = 1;
        $model->sort = 6;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '其他女优';
        $model->user_id = 1;
        $model->sort = 7;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '素人女优';
        $model->user_id = 1;
        $model->sort = 8;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '日本女优';
        $model->user_id = 1;
        $model->sort = 9;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '香港｜韩国三级';
        $model->user_id = 1;
        $model->sort = 10;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = 'H动画';
        $model->user_id = 1;
        $model->sort = 11;
        $model->save();

        $model = new \App\Model\ActorClassification();
        $model->name = '未分類';
        $model->user_id = 1;
        $model->sort = 12;
        $model->save();
    }

    public function down(): void
    {
        \App\Model\ActorClassification::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
