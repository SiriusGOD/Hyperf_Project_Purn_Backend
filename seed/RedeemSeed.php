<?php

declare(strict_types=1);

use App\Util\URand;
use App\Constants\RedeemCode;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class RedeemSeed implements BaseInterface
{
    public function up(): void
    {
        $model = new \App\Model\Redeem();
        $model->title = '優惠劵台北市好捧'.URand::getRandTitle(10);
        $model->code = URand::randomString(10);
        $model->count = rand(1,20);
        $model->category_id = 1;
        $model->category_name = RedeemCode::CATEGORY[1];
        $model->diamond_point = 0;
        $model->vip_days= rand(1,20);
        $model->free_watch = 0;
        $model->status = 0;
        $model->start = date("Y-m-d H:i:s");
        $model->end = date('Y-m-d H:i:s', strtotime('+1 month'));;
        $model->save();

        $model = new \App\Model\Redeem();
        $model->title = '優惠劵台北市好捧'.URand::getRandTitle(10);
        $model->code = URand::randomString(10);
        $model->count = 2;
        $model->category_id = 2;
        $model->category_name = RedeemCode::CATEGORY[2];
        $model->diamond_point = rand(1,20);
        $model->vip_days= 0;
        $model->free_watch = 0;
        $model->status = 0;
        $model->start = date("Y-m-d H:i:s");
        $model->end = date('Y-m-d H:i:s', strtotime('+1 month'));;
        $model->save();

        $model = new \App\Model\Redeem();
        $model->title = '優惠劵台北市好捧'.URand::getRandTitle(10);
        $model->code = URand::randomString(10);
        $model->count = 2;
        $model->category_id = 3;
        $model->category_name = RedeemCode::CATEGORY[3];
        $model->diamond_point = 0;
        $model->vip_days= 0;
        $model->free_watch = rand(1,20);
        $model->status = 0;
        $model->start = date("Y-m-d H:i:s");
        $model->end = date('Y-m-d H:i:s', strtotime('+3 days'));;
        $model->save();


        $model = new \App\Model\Redeem();
        $model->title = '優惠劵台北市好捧'.URand::getRandTitle(10);
        $model->code = URand::randomString(10);
        $model->count = rand(1,20);
        $model->category_id = 3;
        $model->category_name = RedeemCode::CATEGORY[3];
        $model->diamond_point = 0;
        $model->vip_days= 0;
        $model->free_watch = rand(1,20);
        $model->status = 1;
        $model->start = date("Y-m-d H:i:s", strtotime('-11 days'));
        $model->end = date('Y-m-d H:i:s', strtotime('-1 days'));;
        $model->save();

    }

    public function down(): void
    {
        \App\Model\Redeem::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
