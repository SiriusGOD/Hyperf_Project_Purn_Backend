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
use App\Model\ImageGroup;
use App\Model\Member;
use App\Model\MemberLevel;
use App\Model\Order;
use App\Model\Tag;
use App\Model\TagCorrespond;
use App\Model\Video;
use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;

class ImageGroupService
{
    public function storeImageGroup(array $data): ImageGroup
    {
        $model = ImageGroup::withTrashed()->findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->title = $data['title'];
        if (! empty($data['url'])) {
            $model->thumbnail = $data['thumbnail'];
            $model->url = $data['url'];
        }
        $model->description = $data['description'];
        $model->pay_type = $data['pay_type'];
        $model->hot_order = $data['hot_order'];
        $model->deleted_at = $data['deleted_at'] ?? null;
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

        $hideIds = ReportService::getHideIds(ImageGroup::class);
        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(ImageGroup::class);

        $query = ImageGroup::with([
            'tags', 'imagesLimit',
        ])
            ->offset($limit * $page)
            ->limit($limit);

        if (! empty($imageIds)) {
            $query = $query->whereIn('id', $imageIds);
        }

        if (! empty($hideIds)) {
            $withoutIds = array_merge($withoutIds, $hideIds);
        }

        if (! empty($withoutIds)) {
            $query = $query->whereNotIn('id', $withoutIds);
        }

        if (! empty($enableIds)) {
            $query = $query->whereIn('id', $enableIds);
        }

        return $query->where('height', '>', 0)->orderByDesc('id')->get();
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

        $query = ImageGroup::where('title', 'like', '%' . $keyword . '%')
            ->where('height', '>', 0)
            ->offset($limit * $page)
            ->limit($limit);

        if (! empty($sortBy) and $sortBy == Constants::SORT_BY['click']) {
            if ($isAsc == 1) {
                $query = $query->orderBy('total_click');
            } else {
                $query = $query->orderByDesc('total_click');
            }
        } elseif (! empty($sortBy) and $sortBy == Constants::SORT_BY['created_time']) {
            if ($isAsc == 1) {
                $query = $query->orderBy('id');
            } else {
                $query = $query->orderByDesc('id');
            }
        }

        $models = $query->get();
        $imageIds = array_merge($models->pluck('id')->toArray(), $imageIds);

        $query = ImageGroup::with([
            'tags', 'imagesLimit',
        ])
            ->where('height', '>', 0)
            ->offset($limit * $page)
            ->limit($limit);

        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(ImageGroup::class);
        if (! empty($enableIds)) {
            $enableIds = \Hyperf\Collection\collect($imageIds)->diff(\Hyperf\Collection\collect($enableIds));
            $query = $query->whereIn('id', $enableIds->toArray());
        } else {
            $query = $query->whereIn('id', $imageIds);
        }

        if (! empty($hideIds)) {
            $query = $query->whereNotIn('id', $hideIds);
        }

        if (! empty($sortBy) and $sortBy == Constants::SORT_BY['click']) {
            if ($isAsc == 1) {
                $query = $query->orderBy('total_click');
            } else {
                $query = $query->orderByDesc('total_click');
            }
        } elseif (! empty($sortBy) and $sortBy == Constants::SORT_BY['created_time']) {
            if ($isAsc == 1) {
                $query = $query->orderBy('id');
            } else {
                $query = $query->orderByDesc('id');
            }
        }

        return $query->get();
    }

    public function getImageGroupsBySuggest(array $suggest, int $page, int $inputLimit, array $withoutIds = []): array
    {
        $result = [];
        $useImageIds = [];
        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(ImageGroup::class);
        $reportHideIds = ReportService::getHideIds(ImageGroup::class);
        $hideIds = array_merge($reportHideIds, $withoutIds);
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

            $query = ImageGroup::with([
                'tags', 'imagesLimit',
            ])
                ->whereIn('id', $imageIds)
                ->offset($limit * $page)
                ->where('height', '>', 0)
                ->orderByDesc('id')
                ->limit($limit);

            if (! empty($hideIds)) {
                $query = $query->whereNotIn('id', $hideIds);
            }

            if (! empty($enableIds)) {
                $query->whereIn('id', $enableIds);
            }

            $models = $query->get()->toArray();

            $result = array_merge($models, $result);
        }

        return $result;
    }

    public function adminSearchImageGroupQuery(array $params): Builder
    {
        $step = ImageGroup::PAGE_PER;
        $page = $params['page'];
        $query = ImageGroup::withTrashed()->with(['user'])->offset(($page - 1) * $step)
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

        if ($imageGroup->pay_type <= $member->member_level_status or $member->member_level_status == MemberLevel::NO_MEMBER_LEVEL) {
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
        $hideIds = ReportService::getHideIds(ImageGroup::class);
        $query = ImageGroup::with([
            'tags', 'imagesLimit',
        ])
            ->where('hot_order', '>=', 1)
            ->where('height', '>', 0)
            ->offset($limit * $page)
            ->limit($limit)
            ->orderByDesc('hot_order');

        if (! empty($hideIds)) {
            $query = $query->whereNotIn('id', $hideIds);
        }

        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(ImageGroup::class);

        if (! empty($enableIds)) {
            $query = $query->whereIn('id', $enableIds);
        }

        return $query->get()->toArray();
    }

    protected function orderCheck(int $id, int $memberId): bool
    {
        $order = Order::where('orders.user_id', $memberId)
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('products.type', ImageGroup::class)
            ->where('products.id', $id)
            ->where('orders.status', Order::ORDER_STATUS['finish'])
            ->select('orders.currency', 'orders.created_at')
            ->first();
        if (empty($order)) {
            return false;
        }

        // 用免費次數購買的免費商品 過隔天就不顯示在已購買項目中
        if ($order->currency == Order::PAY_CURRENCY['free_quota']) {
            $date1 = Carbon::parse($order->created_at);
            $date2 = Carbon::now();
            $diff = $date1->diff($date2);
            if ($diff > 0) {
                return false;
            }
        }
        return true;
    }
}
