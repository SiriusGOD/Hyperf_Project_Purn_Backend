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
class ImageGroupSeed implements BaseInterface
{
    public function up(): void
    {
        $task = \Hyperf\Support\make(\App\Task\ImageGroupSyncTask::class)->execute();
    }

    public function down(): void
    {
        \App\Model\TagCorrespond::where('correspond_type', \App\Model\ImageGroup::class)->delete();
        \App\Model\ActorCorrespond::where('correspond_type', \App\Model\ImageGroup::class)->delete();
    }

    public function base(): bool
    {
        return false;
    }
}
