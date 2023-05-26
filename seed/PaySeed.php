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
class PaySeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\Pay();
        $model->user_id = 1;
        $model->name = '微信支付';
        $model->pronoun = 'wechat';
        $model->proxy = 'online';
        $model->expire = 0;
        $model->save();

        $model = new \App\Model\Pay();
        $model->user_id = 1;
        $model->name = '银联支付';
        $model->pronoun = 'bankcard';
        $model->proxy = 'online';
        $model->expire = 0;
        $model->save();

        $model = new \App\Model\Pay();
        $model->user_id = 1;
        $model->name = '支付宝支付';
        $model->pronoun = 'alipay';
        $model->proxy = 'online';
        $model->expire = 0;
        $model->save();

        $model = new \App\Model\Pay();
        $model->user_id = 1;
        $model->name = '数字人民币支付';
        $model->pronoun = 'ecny';
        $model->proxy = 'online';
        $model->expire = 0;
        $model->save();


        $model = new \App\Model\Pay();
        $model->user_id = 1;
        $model->name = 'VISA支付';
        $model->pronoun = 'visa';
        $model->proxy = 'online';
        $model->expire = 0;
        $model->save();

        $model = new \App\Model\Pay();
        $model->user_id = 1;
        $model->name = '商家代理支付';
        $model->pronoun = 'agent';
        $model->proxy = 'agent';
        $model->expire = 0;
        $model->save();

    }

    public function down(): void
    {
        \App\Model\Pay::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
