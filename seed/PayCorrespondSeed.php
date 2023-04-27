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
class PayCorrespondSeed implements BaseInterface
{
    public function up(): void
    {
        $products = \App\Model\Product::all();
        $pays = App\Model\Pay::all();

        foreach ($products as $key => $value) {
            foreach ($pays as $key2 => $value2) {
                $model = new \App\Model\PayCorrespond();
                $model->product_id = $value->id;
                $model->pay_id = $value2->id;
                $model->save();
            }
        }
    }

    public function down(): void
    {
        \App\Model\PayCorrespond::truncate();
    }

    public function base(): bool
    {
        return true;
    }
}
