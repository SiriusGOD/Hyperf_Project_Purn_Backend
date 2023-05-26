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
class CoinSeed implements BaseInterface
{
    public function up(): void
    {
        // coin
        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'cash';
        $model->name = '現金100';
        $model->points = 100;
        $model->bonus = 0;
        $model->save();

        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'cash';
        $model->name = '現金200';
        $model->points = 200;
        $model->bonus = 0;
        $model->save();

        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'cash';
        $model->name = '現金500';
        $model->points = 500;
        $model->bonus = 0;
        $model->save();

        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'cash';
        $model->name = '現金1000';
        $model->points = 1000;
        $model->bonus = 0;
        $model->save();

        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'cash';
        $model->name = '現金2000';
        $model->points = 2000;
        $model->bonus = 0;
        $model->save();

        // diamond
        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'diamond';
        $model->name = '鑽石點數 5點';
        $model->points = 5;
        $model->bonus = 0;
        $model->save();

        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'diamond';
        $model->name = '鑽石點數 10點';
        $model->points = 10;
        $model->bonus = 2;
        $model->save();

        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'diamond';
        $model->name = '鑽石點數 20點';
        $model->points = 20;
        $model->bonus = 5;
        $model->save();

        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'diamond';
        $model->name = '鑽石點數 50點';
        $model->points = 50;
        $model->bonus = 20;
        $model->save();

        $model = new \App\Model\Coin();
        $model->user_id = 1;
        $model->type = 'diamond';
        $model->name = '鑽石點數 100點';
        $model->points = 100;
        $model->bonus = 60;
        $model->save();
    }

    public function down(): void
    {
        \App\Model\Coin::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
