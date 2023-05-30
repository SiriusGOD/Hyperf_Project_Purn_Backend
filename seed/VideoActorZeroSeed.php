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
class VideoActorZeroSeed implements BaseInterface
{
    public function up(): void
    {
        $page = 0;
        $limit = 100;
        $forever = true;
        while($forever) {
            $models = \App\Model\Video::offset($page * $limit)
                ->limit($limit)
                ->orderBy('id')
                ->get();

            if (count($models) == 0) {
                $forever = false;
            }
            foreach ($models as $model) {
                $this->createActor($model);
            }
            $page++;
        }
    }

    public function createActor($video): void
    {
        $exist = \App\Model\ActorCorrespond::where('correspond_type', \App\Model\Video::class)
            ->where('correspond_id', $video->id)
            ->exists();
        if($exist) {
            return;
        }
        $model = new \App\Model\ActorCorrespond();
        $model->correspond_type = \App\Model\Video::class;
        $model->correspond_id = $video->id;
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
