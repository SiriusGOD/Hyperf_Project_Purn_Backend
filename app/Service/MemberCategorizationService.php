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
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class MemberCategorizationService extends GenerateService
{
    public const CACHE_KEY = 'member_category:';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function createOrUpdateMemberCategorization(array $params): int
    {
        $model = new MemberCategorization();
        if (! empty($params['id'])) {
            $model = MemberCategorization::find($params['id']);
        }
        $model->member_id = $params['member_id'];
        $model->name = $params['name'];
        $model->hot_order = 0;
        $model->is_default = $params['is_default'] ?? 0;
        $model->is_first = $params['is_first'] ?? 0;
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

    public function updateMemberCategorizationDetails(array $params): void
    {
        MemberCategorizationDetail::whereIn('id', $params['ids'])
            ->update([
                'member_categorization_id' => $params['member_categorization_id'],
            ]);
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

        if (!empty($params['filter'])) {
            $query = $query->where('type', $params['filter']);
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

    public function getCount(int $id) : int
    {
        return MemberCategorizationDetail::where('member_categorization_id', $id)->count();
    }

    public function setDefault(int $memberId, int $id): void
    {
        MemberCategorization::where('member_id', $memberId)->update([
            'is_default' => 0,
        ]);

        MemberCategorization::where('member_id', $memberId)
            ->where('id', $id)
            ->update([
                'is_default' => 1,
            ]);
    }

    public function getDefault(array $params): array
    {
        return [
            'id' => 0,
            'member_id' => $params['member_id'],
            'name' => trans('default.default_all_categorization_name'),
            'is_default' => 0,
            'is_first' => 1,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString(),
            'member_categorization_details' => $this->getDefaultDetail($params),
        ];
    }

    public function getDefaultDetail(array $params): array
    {
        $ids = MemberCategorization::where('member_id', $params['member_id'])
            ->get()
            ->pluck('id')
            ->toArray();
        if (empty($ids)) {
            return [];
        }

        $query = MemberCategorizationDetail::whereIn('member_categorization_id', $ids)
            ->offset($params['page'] * $params['limit'])
            ->limit($params['limit'])
            ->orderByDesc('id');

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

        if (!empty($params['filter'])) {
            $query = $query->where('type', $params['filter']);
        }


        $models = $query->get()->toArray();
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

        $result = [];

        foreach ($collect->toArray() as $row) {
            $result[] = $row;
        }

        return $result;
    }

    public function getDefaultCount(int $memberId) : int
    {
        $ids = MemberCategorization::where('member_id', $memberId)
            ->get()
            ->pluck('id')
            ->toArray();
        if (empty($ids)) {
            return 0;
        }

        return MemberCategorizationDetail::whereIn('member_categorization_id', $ids)->count();
    }

    public function IsMain(int $memberId, array $models): array
    {
        $result = [];
        foreach ($models as $model) {
            $model['member_categorization_details'] = $this->getDetails([
                'id' => $model['id'],
                'page' => 0,
                'limit' => 5,
                'sort_by' => 'created_time',
                'is_asc' => 2,
            ]);

            $result[] = $model;
        }

        array_unshift($result, $this->getDefault([
            'member_id' => $memberId,
            'page' => 0,
            'limit' => 5,
            'sort_by' => 'created_time',
            'is_asc' => 2,
        ]));

        $data = [];
        foreach ($result as $model) {
            $temp = [];
            foreach ($model['member_categorization_details'] as $row) {
                $url = '';
                if (! empty($row['url'])) {
                    $url = $row['url'];
                }

                if (! empty($row['cover_thumb'])) {
                    $url = $row['cover_thumb'];
                }
                $temp[] = $url;
            }
            $model['member_categorization_details'] = $temp;
            $data[] = $model;
        }

        return $data;
    }

    public function updateOrder(array $ids): void
    {
        foreach ($ids as $key => $id) {
            MemberCategorization::where('id', $id)->update([
                'hot_order' => $key + 1,
            ]);
        }
    }

    public function getTypeIdByMemberIdAndType(int $memberId, string $type): array
    {
        $key = $this->getCacheKey($memberId, $type);
        if ($this->redis->exists($key)) {
            return json_decode($this->redis->get($key), true);
        }

        return $this->updateCache($memberId, $type);
    }

    public function updateCache(int $memberId, string $type): array
    {
        $key = $this->getCacheKey($memberId, $type);

        $ids = MemberCategorization::where('member_id', $memberId)->get()->pluck('id')->toArray();
        $typeIds = MemberCategorizationDetail::whereIn('member_categorization_id', $ids)
            ->where('type', $type)
            ->get()
            ->pluck('type_id')
            ->toArray();

        $this->redis->set($key, json_encode($typeIds));

        return $typeIds;
    }

    protected function getVideoDetail(array $models, array $data): array
    {
        $ids = [];
        foreach ($models as $model) {
            if ($model['type'] == Video::class) {
                $ids[] = $model['type_id'];
            }
        }

        $videos = Video::with('tags')->whereIn('id', $ids)->get()->toArray();

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

        $imageGroups = ImageGroup::with(['images', 'tags'])->whereIn('id', $ids)->get()->toArray();

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

    protected function getCacheKey(int $memberId, string $type): string
    {
        $typeKey = match ($type) {
            ImageGroup::class => 'image_group',
            default => 'videos',
        };
        return self::CACHE_KEY . $memberId . ':' . $typeKey;
    }
}
