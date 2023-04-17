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

use App\Model\ImageGroup;
use App\Model\Tag;
use App\Model\TagCorrespond;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;

class ImageGroupService
{
    public function storeImageGroup(array $data): ImageGroup
    {
        $model = ImageGroup::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->title = $data['title'];
        if (! empty($data['url'])) {
            $model->thumbnail = $data['thumbnail'];
            $model->url = $data['url'];
        }
        $model->description = $data['description'];
        $model->save();

        return $model;
    }

    public function getImageGroups(?array $tagIds, int $page): Collection
    {
        $imageIds = [];
        if (! empty($tagIds)) {
            $imageIds = TagCorrespond::where('correspond_type', ImageGroup::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id');
        }

        $query = ImageGroup::with([
            'tags', 'images',
        ])
            ->offset(ImageGroup::PAGE_PER * $page)
            ->limit(ImageGroup::PAGE_PER);

        if (! empty($imageIds)) {
            $query = $query->whereIn('id', $imageIds);
        }

        return $query->get();
    }

    public function getImageGroupsByKeyword(string $keyword, int $page): Collection
    {
        $tagIds = Tag::where('name', 'like', '%' . $keyword . '%')->get()->pluck('id')->toArray();
        $imageIds = [];
        if (! empty($tagIds)) {
            $imageIds = TagCorrespond::where('correspond_type', ImageGroup::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id');
        }

        $query = ImageGroup::with([
            'tags', 'images',
        ])
            ->orWhere('title', 'like', '%' . $keyword . '%')
            ->offset(ImageGroup::PAGE_PER * $page)
            ->limit(ImageGroup::PAGE_PER);

        if (! empty($imageIds)) {
            $query = $query->orWhereIn('id', $imageIds);
        }

        return $query->get();
    }

    public function getImageGroupsBySuggest(array $suggest, int $page): array
    {
        $result = [];
        $useImageIds = [];
        foreach ($suggest as $value) {
            $limit = $value['proportion'] * ImageGroup::PAGE_PER;
            if ($limit < 1) {
                break;
            }

            $imageIds = TagCorrespond::where('correspond_type', ImageGroup::class)
                ->where('tag_id', $value['tag_id'])
                ->whereNotIn('correspond_id', $useImageIds)
                ->get()
                ->pluck('correspond_id')
                ->toArray();

            $useImageIds = array_unique(array_merge($imageIds, $useImageIds));

            $models = ImageGroup::with([
                'tags', 'images',
            ])
                ->whereIn('id', $imageIds)
                ->offset($limit * $page)
                ->limit($limit)
                ->get()
                ->toArray();

            $result = array_merge($models, $result);
        }

        return $result;
    }

    public function adminSearchImageGroupQuery(array $params): Builder
    {
        $step = ImageGroup::PAGE_PER;
        $page = $params['page'];
        $query = ImageGroup::with(['user'])->offset(($page - 1) * $step)
            ->limit($step)
            ->leftJoin('clicks', function ($join) {
                $join->on('image_groups.id', '=', 'clicks.type_id')->where('clicks.type', ImageGroup::class);
            })
            ->leftJoin('likes', function ($join) {
                $join->on('image_groups.id', '=', 'likes.type_id')->where('likes.type', ImageGroup::class);
            })
            ->select('image_groups.*', Db::raw('clicks.count as click_count'), Db::raw('likes.count as like_count'));

        if (! empty($params['title'])) {
            $query = $query->where('title', 'like', '%' . $params['title'] . '%');
        }

        if (! empty($params['tag_ids'])) {
            $ids = TagCorrespond::where('correspond_type', ImageGroup::class)
                ->whereIn('tag_id', $params['tag_ids'])
                ->get()
                ->pluck('correspond_id')
                ->toArray();

            $query = $query->whereIn('image_groups.id', $ids);
        }

        return $query;
    }
}
