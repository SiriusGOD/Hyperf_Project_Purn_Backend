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
class ResetImageGroupSeed implements BaseInterface
{
    public function up(): void
    {

        \App\Model\ImageGroup::truncate();
        \App\Model\Image::truncate();

    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return false;
    }
}
