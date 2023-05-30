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
class ImageActorZeroSeed implements BaseInterface
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
                $this->createActor($imageGroup);
            }
            $page++;
        }
    }

    public function createActor($imageGroup): void
    {
        $exist = \App\Model\ActorCorrespond::where('correspond_type', \App\Model\ImageGroup::class)
            ->where('correspond_id', $imageGroup->id)
            ->exists();
        if($exist) {
            return;
        }
        $model = new \App\Model\ActorCorrespond();
        $model->correspond_type = \App\Model\ImageGroup::class;
        $model->correspond_id = $imageGroup->id;
        $model->actor_id = 0;
        $model->save();
    }


    public function down(): void
    {

    }

    public function base(): bool
    {
        return false;
    }
}
