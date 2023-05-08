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
class TagTypeSeed implements BaseInterface
{
    public function up(): void
    {
        $page = 0;
        $limit = 100;
        $forever = true;
        while($forever) {
            $models = \App\Model\TagCorrespond::offset($page * $limit)
                ->limit($limit)
                ->orderBy('id')
                ->get();

            if ($models->isEmpty()) {
                $forever = false;
            }

            foreach ($models as $model) {
                $this->updateType($model);
            }
            $page++;
        }

    }

    protected function updateType(\App\Model\TagCorrespond $model)
    {
        $arr = [
            'member' => \App\Model\MemberLevel::class,
            'points' => \App\Model\Coin::class,
            'video' => \App\Model\Video::class,
            'image_group' => \App\Model\ImageGroup::class
        ];

        $type = $arr[$model->correspond_type] ?? null;
        if (!empty($type)) {
            $model->correspond_type = $type;
            $model->save();
        }
    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return false;
    }
}
