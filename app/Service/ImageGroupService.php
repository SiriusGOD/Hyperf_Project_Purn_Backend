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
use App\Model\Actor;
use App\Model\ActorCorrespond;
use App\Model\BuyMemberLevel;
use App\Model\Image;
use App\Model\ImageGroup;
use App\Model\Member;
use App\Model\MemberLevel;
use App\Model\Order;
use App\Model\Tag;
use App\Model\TagCorrespond;
use Carbon\Carbon;
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
        $model->pay_type = $data['pay_type'];
        $model->hot_order = $data['hot_order'];
        $model->save();

        return $model;
    }

    public function getImageGroups(?array $tagIds, int $page, $limit = ImageGroup::PAGE_PER, array $withoutIds = []): Collection
    {
        $imageIds = [];
        if (! empty($tagIds)) {
            $imageIds = TagCorrespond::where('correspond_type', ImageGroup::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id');
        }

        $query = ImageGroup::with([
            'tags', 'imagesLimit',
        ])
            ->offset($limit * $page)
            ->limit($limit);

        if (! empty($imageIds)) {
            $query = $query->whereIn('id', $imageIds);
        }

        if (! empty($withoutIds)) {
            $query = $query->whereNotIn('id', $withoutIds);
        }

        return $query->orderByDesc('id')->get();
    }

    public function getImageGroupsByKeyword(string $keyword, int $page, int $limit, ?int $sortBy = null, ?int $isAsc = null): Collection
    {
        $tagIds = Tag::where('name', 'like', '%' . $keyword . '%')->get()->pluck('id')->toArray();
        $imageIds = [];
        if (! empty($tagIds)) {
            $imageIds = TagCorrespond::where('correspond_type', ImageGroup::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id')
                ->toArray();
        }

        $actorIds = Actor::where('name', 'like', '%' . $keyword . '%')->get()->pluck('id')->toArray();
        if (! empty($actorIds)) {
            $result = ActorCorrespond::where('correspond_type', ImageGroup::class)
                ->whereIn('actor_id', $actorIds)
                ->get()
                ->pluck('correspond_id')
                ->toArray();
            $imageIds = array_merge($imageIds, $result);
        }

        $query = ImageGroup::with([
            'tags', 'imagesLimit',
        ])
            ->orWhere('title', 'like', '%' . $keyword . '%')
            ->offset($limit * $page)
            ->limit($limit);

        if (! empty($imageIds)) {
            $query = $query->orWhereIn('id', $imageIds);
        }

        if (! empty($sortBy) and $sortBy == Constants::SORT_BY['click']) {
            if ($isAsc == 1) {
                $query = $query->orderBy('total_click');
            } else {
                $query = $query->orderByDesc('total_click');
            }
        } elseif(! empty($sortBy) and $sortBy == Constants::SORT_BY['created_time']) {
            if ($isAsc == 1) {
                $query = $query->orderBy('id');
            } else {
                $query = $query->orderByDesc('id');
            }
        }

        return $query->get();
    }

    public function getImageGroupsBySuggest(array $suggest, int $page, int $inputLimit = ImageGroup::PAGE_PER): array
    {
        $result = [];
        $useImageIds = [];
        foreach ($suggest as $value) {
            $limit = $value['proportion'] * $inputLimit;
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
                'tags', 'imagesLimit',
            ])
                ->whereIn('id', $imageIds)
                ->offset($limit * $page)
                ->orderByDesc('id')
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

    public function isPay(int $id, int $memberId): bool
    {
        $member = Member::find($memberId);
        $imageGroup = ImageGroup::find($id);

        if ($imageGroup->pay_type > $member->member_level_status or $member->member_level_status == MemberLevel::NO_MEMBER_LEVEL) {
            return $this->orderCheck($id, $memberId);
        }

        $memberLevelType = array_flip(MemberLevel::TYPE_VALUE);
        $buyMemberLevel = BuyMemberLevel::where('member_id', $memberId)->where('member_level_type', $memberLevelType[$member->member_level_status])->first();
        if (empty($buyMemberLevel)) {
            return false;
        }
        $endTime = Carbon::parse($buyMemberLevel->end_time);
        $startTime = Carbon::parse($buyMemberLevel->start_time);
        $diff = $endTime->diffInDays($startTime);
        if (abs($diff) > 1) {
            return true;
        }

        return $this->orderCheck($id, $memberId);
    }

    public function getImageGroupsByHotOrder(int $page, int $limit): array
    {
        return ImageGroup::with([
            'tags', 'imagesLimit',
        ])
            ->where('hot_order', '>=', 1)
            ->offset($limit * $page)
            ->limit($limit)
            ->orderByDesc('hot_order')
            ->get()
            ->toArray();
    }

    protected function orderCheck(int $id, int $memberId): bool
    {
        return Order::where('orders.user_id', $memberId)
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('products.type', ImageGroup::class)
            ->where('products.correspond_id', $id)
            ->where('orders.status', Order::ORDER_STATUS['finish'])
            ->exists();
    }
}
