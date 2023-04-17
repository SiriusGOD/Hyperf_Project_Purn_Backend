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
namespace App\Service;

use App\Model\Click;
use App\Model\ClickDetail;

class LikeService
{
    public function addLike(string $type, int $typeId): void
    {
        $model = Click::where('type', $type)
            ->where('type_id', $typeId)
            ->first();

        if (empty($model)) {
            $model = $this->createLike([
                'type' => $type,
                'type_id' => $typeId,
            ]);
        }

        ++$model->count;
        $model->save();

        if (auth('jwt')->check()) {
            $this->createLikeDetail($model->id, auth('jwt')->user()->getId());
        }
    }

    public function createLikeDetail(int $clickId, int $memberId): void
    {
        $model = new ClickDetail();
        $model->click_id = $clickId;
        $model->member_id = $memberId;
        $model->save();
    }

    private function createLike(array $data): Click
    {
        $model = new Click();
        $model->type = $data['type'];
        $model->type_id = $data['type_id'];
        $model->count = 0;

        return $model;
    }
}
