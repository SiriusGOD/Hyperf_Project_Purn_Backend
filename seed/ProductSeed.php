<?php

declare(strict_types=1);

use App\Model\Coin;
use App\Model\MemberLevel;
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
        $coins = Coin::where('type', 'cash')->get();
        foreach ($coins as $key => $coin) {
            // CNY
            $model = new \App\Model\Product();
            $model->user_id = 1;
            $model->type = Coin::class;
            $model->correspond_id = $coin -> id;
            $model->name = $coin -> points;
            $model->expire = 0;
            $model->start_time = '2023-03-25 00:00:00';
            $model->end_time = '2033-03-25 00:00:00';
            $model->currency = 'CNY';
            $model->selling_price = $coin -> points;
            $model->save();

            // TWD
            $model = new \App\Model\Product();
            $model->user_id = 1;
            $model->type = Coin::class;
            $model->correspond_id = $coin -> id;
            $model->name = $coin -> points;
            $model->expire = 0;
            $model->start_time = '2023-03-25 00:00:00';
            $model->end_time = '2033-03-25 00:00:00';
            $model->currency = 'TWD';
            $model->selling_price = $coin -> points;
            $model->save();
        }

        // points diamond
        $diamonds = Coin::where('type', 'diamond')->get();
        foreach ($diamonds as $key => $diamond) {
            $model = new \App\Model\Product();
            $model->user_id = 1;
            $model->type = Coin::class;
            $model->correspond_id = $diamond -> id;
            $model->name = $diamond -> points;
            $model->expire = 0;
            $model->start_time = '2023-03-25 00:00:00';
            $model->end_time = '2033-03-25 00:00:00';
            $model->currency = 'COIN';
            $model->selling_price = ($diamond -> points) * 10;
            $model->save();
        }

        // member vip
        $vips = MemberLevel::where('type', 'vip')->get();
        foreach ($vips as $key => $vip) {
            switch ($vip -> duration) {
                case 1:
                    $price = 40;
                    break;
                case 30:
                    $price = 100;
                    break;
                case 90:
                    $price = 200;
                    break;
                case 3650:
                    $price = 250;
                    break;
            }

            // CNY
            $model = new \App\Model\Product();
            $model->user_id = 1;
            $model->type = MemberLevel::class;
            $model->correspond_id = $vip -> id;
            $model->name = str_replace('VIP卡', '', $vip -> name);
            $model->expire = 0;
            $model->start_time = '2023-03-25 00:00:00';
            $model->end_time = '2033-03-25 00:00:00';
            $model->currency = 'CNY';
            $model->selling_price = $price;
            $model->save();

            // TWD
            $model = new \App\Model\Product();
            $model->user_id = 1;
            $model->type = MemberLevel::class;
            $model->correspond_id = $vip -> id;
            $model->name = str_replace('VIP卡', '', $vip -> name);
            $model->expire = 0;
            $model->start_time = '2023-03-25 00:00:00';
            $model->end_time = '2033-03-25 00:00:00';
            $model->currency = 'TWD';
            $model->selling_price = $price;
            $model->save();
        }

        // member diamond
        $diamonds = MemberLevel::where('type', 'diamond')->get();
        foreach ($diamonds as $key => $diamond) {
            switch ($diamond -> duration) {
                case 1:
                    $price = 50;
                    break;
                case 30:
                    $price = 200;
                    break;
            }

            // CNY
            $model = new \App\Model\Product();
            $model->user_id = 1;
            $model->type = MemberLevel::class;
            $model->correspond_id = $diamond -> id;
            $model->name = str_replace('鑽石卡', '', $diamond -> name);
            $model->expire = 0;
            $model->start_time = '2023-03-25 00:00:00';
            $model->end_time = '2033-03-25 00:00:00';
            $model->currency = 'CNY';
            $model->selling_price = $price;
            $model->save();

            // TWD
            $model = new \App\Model\Product();
            $model->user_id = 1;
            $model->type = MemberLevel::class;
            $model->correspond_id = $diamond -> id;
            $model->name = str_replace('鑽石卡', '', $diamond -> name);
            $model->expire = 0;
            $model->start_time = '2023-03-25 00:00:00';
            $model->end_time = '2033-03-25 00:00:00';
            $model->currency = 'TWD';
            $model->selling_price = $price;
            $model->save();
        }
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
