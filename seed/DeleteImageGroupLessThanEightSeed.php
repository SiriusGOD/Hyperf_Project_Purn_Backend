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
class DeleteImageGroupLessThanEightSeed implements BaseInterface
{
    public function up(): void
    {
        $ids = \App\Model\Image::withTrashed()
            ->groupBy('group_id')
            ->select(\Hyperf\DbConnection\Db::raw('count(*) as total'),'group_id')
            ->havingRaw('count(*) < ?', [8])
            ->get()
            ->pluck('group_id')
            ->toArray();

        \App\Model\Image::withTrashed()->whereIn('group_id', $ids)->forceDelete();
        \App\Model\ImageGroup::withTrashed()->whereIn('id', $ids)->forceDelete();
    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return false;
    }
}
