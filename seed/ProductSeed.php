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
        // points cash
        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 1;
        $model->name = '100';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 100;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 2;
        $model->name = '200';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 200;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 3;
        $model->name = '500';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 500;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 4;
        $model->name = '1000';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 1000;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 5;
        $model->name = '2000';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 2000;
        $model->save();

        // points diamond
        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 6;
        $model->name = '5';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'COIN';
        $model->selling_price = 50;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 7;
        $model->name = '10';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'COIN';
        $model->selling_price = 100;
        $model->save();
        
        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 8;
        $model->name = '20';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'COIN';
        $model->selling_price = 200;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 9;
        $model->name = '50';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'COIN';
        $model->selling_price = 500;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'points';
        $model->correspond_id = 10;
        $model->name = '100';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'COIN';
        $model->selling_price = 1000;
        $model->save();

        // member vip
        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'member';
        $model->correspond_id = 1;
        $model->name = '1天';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 40;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'member';
        $model->correspond_id = 2;
        $model->name = '30天';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 100;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'member';
        $model->correspond_id = 3;
        $model->name = '90天';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 200;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'member';
        $model->correspond_id = 4;
        $model->name = '永久';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 250;
        $model->save();

        // member diamond
        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'member';
        $model->correspond_id = 5;
        $model->name = '1天';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 50;
        $model->save();

        $model = new \App\Model\Product();
        $model->user_id = 1;
        $model->type = 'member';
        $model->correspond_id = 6;
        $model->name = '30天';
        $model->expire = 0;
        $model->start_time = '2023-03-25 00:00:00';
        $model->end_time = '2033-03-25 00:00:00';
        $model->currency = 'CNY';
        $model->selling_price = 200;
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
