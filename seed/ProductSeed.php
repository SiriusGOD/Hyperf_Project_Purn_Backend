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
class ProductSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'App\Model\Image';
        $model->correspond_id = 1;
        $model->name = '麥香奶茶';
        $model->expire = 1;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2024-03-25 00:00:00';
        $model->currency = '美金';
        $model->selling_price = 10;
        $model->save();
        
        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'App\Model\Image';
        $model->correspond_id = 2;
        $model->name = '麥香綠茶';
        $model->expire = 1;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2024-03-25 00:00:00';
        $model->currency = '人民幣';
        $model->selling_price = 2;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'App\Model\Image';
        $model->correspond_id = 3;
        $model->name = '台幣';
        $model->expire = 1;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2024-03-25 00:00:00';
        $model->currency = '美金';
        $model->selling_price = 100;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'App\Model\Video';
        $model->correspond_id = 1;
        $model->name = '你的名字';
        $model->expire = 1;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2024-03-25 00:00:00';
        $model->currency = '人民幣';
        $model->selling_price = 2;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'App\Model\Video';
        $model->correspond_id = 2;
        $model->name = '鈴芽之旅';
        $model->expire = 1;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2024-03-25 00:00:00';
        $model->currency = '美金';
        $model->selling_price = 100;
        $model->save();

    }

    public function down(): void
    {
        \App\Model\Product::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
