<?php

declare(strict_types=1);

use App\Model\Product;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class CreateImageGroupProductSeed implements BaseInterface
{
    public function up(): void
    {
        $page = 0;
        $limit = 100;
        $forever = true;
        while($forever) {
            $imageGroups = \App\Model\ImageGroup::where('sync_id', '>=', 1)
                ->offset($page * $limit)
                ->limit($limit)
                ->get();

            if (count($imageGroups) == 0) {
                $forever = false;
            }
            foreach ($imageGroups as $imageGroup) {
                $this->createProduct($imageGroup);
            }
            $page++;
        }

    }

    protected function createProduct(\App\Model\ImageGroup $model)
    {
        $data['id'] = null;
        $data['type'] = Product::TYPE_CORRESPOND_LIST['image'];
        $data['correspond_id'] = $model->id;
        $data['name'] = $model->title;
        $data['user_id'] = 1;
        $data['expire'] = 0;
        $data['start_time'] = date('Y-m-d H:i:s');
        $data['end_time'] = \Carbon\Carbon::now()->addYears(10)->toDateTimeString();
        $data['currency'] = 'COIN';
        $data['diamond_price'] = 1;
        $data['selling_price'] = 0;
        \Hyperf\Support\make(\App\Service\ProductService::class)->store($data);
    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return false;
    }
}
