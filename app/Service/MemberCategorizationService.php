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

use App\Constants\Constants;
use App\Model\ImageGroup;
use App\Model\MemberCategorization;
use App\Model\MemberCategorizationDetail;
use App\Model\Video;

class MemberCategorizationService extends GenerateService
{
    public function createOrUpdateMemberCategorization(array $params): int
    {
        $model = new MemberCategorization();
        if (! empty($params['id'])) {
            $model = MemberCategorization::find($params['id']);
        }
        $model->member_id = $params['member_id'];
        $model->name = $params['name'];
        $model->hot_order = $params['hot_order'] ?? 0;
        $model->is_default = $params['is_default'] ?? 0;
        $model->save();

        return $model->id;
    }

    public function createMemberCategorizationDetail(array $params): void
    {
        $model = MemberCategorizationDetail::where('member_categorization_id', $params['member_categorization_id'])
            ->where('type', $params['type'])
            ->where('type_id', $params['type_id'])
            ->first();

        if (! empty($model)) {
            return;
        }

        $model = new MemberCategorizationDetail();
        $model->member_categorization_id = $params['member_categorization_id'];
        $model->type = $params['type'];
        $model->type_id = $params['type_id'];
        $model->total_click = 0;
        $model->save();
    }

    public function updateMemberCategorizationDetail(array $params): void
    {
        $model = MemberCategorizationDetail::find($params['id']);
        $model->member_categorization_id = $params['member_categorization_id'];
        $model->save();
    }

    public function getDetails(array $params): array
    {
        $query = MemberCategorizationDetail::where('member_categorization_id', $params['id'])
            ->offset($params['page'] * $params['limit'])
            ->limit($params['limit']);
        if (! empty($params['sort_by']) and $params['sort_by'] == Constants::SORT_BY['click']) {
            if ($params['is_asc'] == 1) {
                $query = $query->orderBy('total_click');
            } else {
                $query = $query->orderByDesc('total_click');
            }
        } elseif (! empty($params['sort_by']) and $params['sort_by'] == Constants::SORT_BY['created_time']) {
            if ($params['is_asc'] == 1) {
                $query = $query->orderBy('id');
            } else {
                $query = $query->orderByDesc('id');
            }
        }

        $models = $query->get();
        if (empty($models)) {
            return [];
        }
        $models = $models->toArray();
        $result = [];
        $result = $this->getVideoDetail($models, $result);

        $collect = \Hyperf\Collection\collect($this->getImageGroupsDetail($models, $result));

        if (! empty($params['sort_by']) and $params['sort_by'] == Constants::SORT_BY['click']) {
            if ($params['is_asc'] == 1) {
                $collect = $collect->sortBy('total_click');
            } else {
                $collect = $collect->sortByDesc('total_click');
            }
        } elseif (! empty($params['sort_by']) and $params['sort_by'] == Constants::SORT_BY['created_time']) {
            if ($params['is_asc'] == 1) {
                $collect = $collect->sortBy('member_categorization_detail_id');
            } else {
                $collect = $collect->sortByDesc('member_categorization_detail_id');
            }
        }

        $rows = [];
        foreach ($collect->toArray() as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function setDefault(int $memberId, int $id): void
    {
        MemberCategorization::where('member_id', $memberId)->update([
            'iis_default' => 0,
        ]);

        MemberCategorization::where('member_id', $memberId)
            ->where('id', $id)
            ->update([
                'is_default' => 1,
            ]);
    }

    protected function getVideoDetail(array $models, array $data): array
    {
        $ids = [];
        foreach ($models as $model) {
            if ($model['type'] == Video::class) {
                $ids[] = $model['type_id'];
            }
        }

        $videos = Video::whereIn('id', $ids)->get()->toArray();

        $result = [];
        foreach ($videos as $video) {
            foreach ($models as $model) {
                if ($model['type_id'] == $video['id'] and $model['type'] == Video::class) {
                    $video['member_categorization_detail_id'] = $model['id'];
                    $result[] = $video;
                }
            }
        }

        return $this->generateVideos($data, $result);
    }

    protected function getImageGroupsDetail(array $models, array $data): array
    {
        $ids = [];
        foreach ($models as $model) {
            if ($model['type'] == ImageGroup::class) {
                $ids[] = $model['type_id'];
            }
        }

        $imageGroups = ImageGroup::with('images')->whereIn('id', $ids)->get()->toArray();

        $result = [];
        foreach ($imageGroups as $imageGroup) {
            foreach ($models as $model) {
                if ($model['type_id'] == $imageGroup['id'] and $model['type'] == ImageGroup::class) {
                    $imageGroup['member_categorization_detail_id'] = $model['id'];
                    $result[] = $imageGroup;
                }
            }
        }

        return $this->generateImageGroups($data, $result);
    }
}
