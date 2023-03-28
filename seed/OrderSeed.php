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
class OrderSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\Order();
        $model->user_id = 1;
        $model->order_number = 'PO2023031700001';
        $model->address = '台北市信義區仁愛路四段505號';
        $model->email = 'test01@gmail.com';
        $model->mobile = '0212345678';
        $model->telephone = '09123456789';
        $model->payment_type = 1;
        $model->currency = '台幣';
        $model->pay_way = 'wechat';
        $model->pay_url = 'http://test/order/payc.php?id=20200629000216561009';
        $model->pay_proxy = 'online';
        $model->total_price = 20;
        $model->status = 11;
        $model->save();

        $model = new \App\Model\Order();
        $model->user_id = 2;
        $model->order_number = 'PO2023031700002';
        $model->address = '台北市信義區仁愛路四段100號';
        $model->email = 'test02@gmail.com';
        $model->mobile = '0222222222';
        $model->telephone = '0911111111';
        $model->payment_type = 1;
        $model->currency = '台幣';
        $model->pay_way = 'wechat';
        $model->pay_url = 'http://test/order/payc.php?id=20200629000216561010';
        $model->pay_proxy = 'online';
        $model->total_price = 100;
        $model->status = 1;
        $model->save();

    }

    public function down(): void
    {
        \App\Model\Order::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
