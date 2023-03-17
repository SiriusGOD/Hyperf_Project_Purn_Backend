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
class OrderDetailSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\OrderDetail();
        $model->order_id = 1;
        $model->product_id = 1;
        $model->product_name = '麥香奶茶';
        $model->product_currency = '台幣';
        $model->product_selling_price = 10;
        $model->save();

        $model = new \App\Model\OrderDetail();
        $model->order_id = 1;
        $model->product_id = 2;
        $model->product_name = '麥香綠茶';
        $model->product_currency = '台幣';
        $model->product_selling_price = 10;
        $model->save();

        $model = new \App\Model\OrderDetail();
        $model->order_id = 2;
        $model->product_id = 3;
        $model->product_name = '大麥克';
        $model->product_currency = '台幣';
        $model->product_selling_price = 100;
        $model->save();

    }

    public function down(): void
    {
        \App\Model\OrderDetail::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
