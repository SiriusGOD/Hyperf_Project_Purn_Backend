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
use App\Model\Navigation;
use App\Model\Video;
use Hyperf\HttpServer\Contract\RequestInterface;

class NavigationService extends GenerateService
{
    public const HOT_ORDER_IMAGE_GROUP_PERCENT = 0.1;

    public const HOT_ORDER_VIDEO_PERCENT = 0.1;

    public const ADVERTISEMENT_PAGE_PER = 20;

    public const OTHER_LIMIT = 1;

    public const DETAIL_PERCENTS = [
        0 => [0.1, 0.9],
        1 => [0.2, 0.8],
        2 => [0.2, 0.8],
        3 => [0.1, 0.9],
    ];

    public VideoService $videoService;

    public ImageGroupService $imageGroupService;

    public AdvertisementService $advertisementService;

    public TagService $tagService;

    public string $url;

    public function __construct(
        ImageGroupService $imageGroupService,
        VideoService $videoService,
        AdvertisementService $advertisementService,
        TagService $tagService,
        RequestInterface $request
    ) {
        $this->imageGroupService = $imageGroupService;
        $this->videoService = $videoService;
        $this->advertisementService = $advertisementService;
        $this->tagService = $tagService;
        $this->url = $request->url();
    }

    public function navigationDetail(array $suggest, int $navId, string $type, int $id, int $page, int $limit): array
    {
        $imageLimit = (int) floor($limit / 2);
        $tagIds = $this->tagService->getTagsByModelType($type, $id);
        $imageGroups = $this->navigationDetailImageGroups($suggest, $navId, $tagIds, $page, $imageLimit);
        $videoLimit = $limit - $imageLimit;
        $videos = $this->navigationDetailVideos($suggest, $navId, $tagIds, $page, $videoLimit);
        $returnResult = $this->generateImageGroups([], $imageGroups);
        $returnResult = $this->generateVideos($returnResult, $videos);

        switch ($navId) {
            case 1:
                $collect = \Hyperf\Collection\collect($returnResult);
                $collect = $collect->sortByDesc('total_click');

                $returnResult = $collect->toArray();
                break;
            default:
                $collect = \Hyperf\Collection\collect($returnResult);
                $collect = $collect->sortByDesc('created_at');

                $returnResult = $collect->toArray();
        }

        $items = [];
        foreach ($returnResult as $value) {
            $items[] = $value;
        }

        return $items;
    }

    public function navigationSuggest(array $suggest, int $page, int $limit): array
    {
        $advertisementLimitArr = $this->getAdvertisementsLimit($page, $limit);
        if ($advertisementLimitArr['limit'] > 0) {
            --$limit;
        }
        $imageGroupLimit = (int) floor($limit / 2);
        $videoLimit = $limit - $imageGroupLimit;

        $imageGroups = $this->navigationSuggestImageGroups($suggest, $page, $imageGroupLimit);
        $videos = $this->navigationSuggestVideos($suggest, $page, $videoLimit);
        $advertisements = $this->advertisementService->getAdvertisementBySearch($advertisementLimitArr['last_page'], $advertisementLimitArr['limit']);

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    public function navigationSuggestByMemberCategorization(array $suggest, int $page, int $limit, int $memberId): array
    {
        $advertisementLimitArr = $this->getAdvertisementsLimit($page, $limit);
        if ($advertisementLimitArr['limit'] > 0) {
            $limit = $limit - $advertisementLimitArr['limit'];
        }
        $imageGroupLimit = (int) floor($limit / 2);
        $videoLimit = $limit - $imageGroupLimit;

        $hideIds = \Hyperf\Support\make(MemberCategorizationService::class)->getTypeIdByMemberIdAndType($memberId, ImageGroup::class);
        $imageGroups = $this->navigationSuggestImageGroupsByMemberCategorization($suggest, $page, $imageGroupLimit, $hideIds);

        $hideIds = \Hyperf\Support\make(MemberCategorizationService::class)->getTypeIdByMemberIdAndType($memberId, Video::class);
        $videos = $this->navigationSuggestVideosWithMemberCategorization($suggest, $page, $videoLimit, $hideIds);

        $advertisements = $this->advertisementService->getAdvertisementBySearch($advertisementLimitArr['last_page'], $advertisementLimitArr['limit']);

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    public function navigationPopular(array $suggest, int $page, int $limit): array
    {
        $advertisementLimitArr = $this->getAdvertisementsLimit($page, $limit);
        if ($advertisementLimitArr['limit'] > 0) {
            $limit = $limit - $advertisementLimitArr['limit'];
        }
        $imageGroupLimit = (int) floor($limit / 2);
        $videoLimit = $limit - $imageGroupLimit;

        $imageGroups = $this->navigationPopularImageGroups($suggest, $page, $imageGroupLimit);
        $videos = $this->navigationPopularVideos($suggest, $page, $videoLimit);
        $advertisements = $this->advertisementService->getAdvertisementBySearch($advertisementLimitArr['last_page'], $advertisementLimitArr['limit']);

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    public function navigationSuggestSortById(array $suggest, int $page, int $limit): array
    {
        $result = $this->navigationSuggest($suggest, $page, $limit);
        $collect = \Hyperf\Collection\collect($result);
        $collect = $collect->sortByDesc('created_at');
        $result = [];

        foreach ($collect->toArray() as $row) {
            $result[] = $row;
        }

        return $result;
    }

    public function createNavigation(array $params): void
    {
        $model = Navigation::where('id', $params['id'])->first();
        if (empty($model)) {
            $model = new Navigation();
        }

        $model->name = $params['name'];
        $model->hot_order = $params['hot_order'];
        $model->save();
    }

    protected function navigationDetailImageGroups(array $suggest, int $navId, array $tagIds, int $page, int $limit): array
    {
        $percent = self::DETAIL_PERCENTS[$navId] ?? [0.2, 0.8];
        $typeLimit = (int) floor($percent[1] * $limit);
        $hideIds = ReportService::getHideIds(ImageGroup::class);

        $imageGroupIds = $this->tagService->getTypeIdsByTagIds($tagIds, ImageGroup::class, $page, $limit);
        $query = ImageGroup::with([
            'tags', 'imagesLimit',
        ])
            ->whereIn('id', $imageGroupIds)
            ->whereNotIn('id', $hideIds)
            ->where('height', '>', 0)
            ->offset($typeLimit * $page)
            ->limit($typeLimit);

        if (! empty($hideIds)) {
            $query = $query->whereNotIn('id', $hideIds);
        }

        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(ImageGroup::class);

        if (! empty($enableIds)) {
            $query = $query->whereIn('id', $enableIds);
        }

        $imageGroups = $query
            ->get()
            ->toArray();

        $userLimit = $limit - count($imageGroups);

        $suggestModels = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, $userLimit);
        $remain = $userLimit - count($suggestModels);


        $models = $this->imageGroupService->getImageGroups(null, $page, $remain)->toArray();

        return array_merge($models, $suggestModels, $imageGroups);
    }

    protected function navigationDetailVideos(array $suggest, int $navId, array $tagIds, int $page, int $limit): array
    {
        $percent = self::DETAIL_PERCENTS[$navId] ?? [0.2, 0.8];
        $typeLimit = (int) floor($percent[1] * $limit);
        $hideIds = ReportService::getHideIds(Video::class);

        $ids = $this->tagService->getTypeIdsByTagIds($tagIds, Video::class, $page, $limit);
        $query = Video::with([
            'tags',
        ])
            ->whereIn('id', $ids)
            ->offset($typeLimit * $page)
            ->limit($typeLimit)
            ->where('cover_height', '>', 0);

        if (! empty($hideIds)) {
            $query = $query->whereNotIn('id', $hideIds);
        }

        $enableIds = \Hyperf\Support\make(ProductService::class)->getEnableIds(Video::class);
        if (! empty($enableIds)) {
            $query = $query->whereIn('id', $enableIds);
        }

        $videos = $query->get()
            ->toArray();

        $userLimit = $limit - count($videos);

        $suggestModels = $this->videoService->getVideosBySuggest($suggest, $page, $userLimit);
        $remain = $userLimit - count($suggestModels);

        $models = $this->videoService->getVideos(null, $page, 9, $remain)->toArray();

        return array_merge($models, $suggestModels, $videos);
    }

    protected function navigationPopularImageGroups(array $suggest, int $page, int $limit): array
    {
        $otherLimit = self::OTHER_LIMIT;
        $suggestLimit = $limit - $otherLimit;
        $suggestModels = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, $suggestLimit);
        $remain = $limit - count($suggestModels);


        $models = $this->imageGroupService->getImageGroups(null, $page, $remain)->toArray();

        $result = array_merge($suggestModels, $models);
        $collect = \Hyperf\Collection\collect($result);
        $arr = $collect->sortByDesc('total_click');

        $returnResult = [];
        foreach ($arr as $value) {
            $returnResult[] = $value;
        }

        return $returnResult;
    }

    protected function navigationPopularVideos(array $suggest, int $page, int $limit): array
    {
        $otherLimit = self::OTHER_LIMIT;
        $suggestLimit = $limit - $otherLimit;
        $suggestModels = $this->videoService->getVideosBySuggest($suggest, $page, $suggestLimit);
        $remain = $limit - count($suggestModels);

        $models = $this->videoService->getVideos(null, $page, 9, $remain)->toArray();

        $result = array_merge($suggestModels, $models);

        $collect = \Hyperf\Collection\collect($result);
        $arr = $collect->sortByDesc('total_click');

        $returnResult = [];
        foreach ($arr as $value) {
            $returnResult[] = $value;
        }

        return $returnResult;
    }

    protected function calculateNavigationPopularClick(string $type, array $ids): array
    {
        $clickService = make(ClickService::class);
        return $clickService->calculatePopularClickByTypeIds($type, $ids);
    }

    protected function navigationSuggestImageGroupsByMemberCategorization(array $suggest, int $page, int $limit, array $hideIds): array
    {
        $suggestModels = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, $limit, $hideIds);
        $remain = $limit - count($suggestModels);

        $models = $this->imageGroupService->getImageGroups(null, $page, $remain, $hideIds)->toArray();

        return array_merge($suggestModels, $models);
    }

    protected function navigationSuggestImageGroups(array $suggest, int $page, int $limit): array
    {
        $hotOrderLimit = $this->getHotOrderPerLimit(ImageGroup::class, $limit);
        $hotOrderModels = $this->imageGroupService->getImageGroupsByHotOrder($page, $hotOrderLimit);
        $otherLimit = self::OTHER_LIMIT;
        $suggestLimit = $limit - count($hotOrderModels) - $otherLimit;

        $suggestModels = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, $suggestLimit);

        $remain = $limit - count($suggestModels) - count($hotOrderModels);

        $models = $this->imageGroupService->getImageGroups(null, $page, $remain)->toArray();

        return array_merge($hotOrderModels, $suggestModels, $models);
    }

    protected function navigationSuggestVideos(array $suggest, int $page, int $limit): array
    {
        $hotOrderLimit = $this->getHotOrderPerLimit(Video::class, $limit);
        $hotOrderModels = $this->videoService->getVideosByHotOrder($page, $hotOrderLimit);
        $otherLimit = self::OTHER_LIMIT;
        $suggestLimit = $limit - count($hotOrderModels) - $otherLimit;

        $suggestModels = $this->videoService->getVideosBySuggest($suggest, $page, $suggestLimit);

        $remain = $limit - count($suggestModels) - count($hotOrderModels);

        $models = $this->videoService->getVideos(null, $page, 9, $remain)->toArray();

        return array_merge($hotOrderModels, $suggestModels, $models);
    }

    protected function navigationSuggestVideosWithMemberCategorization(array $suggest, int $page, int $limit, array $hideIds): array
    {
        $suggestModels = $this->videoService->getVideosBySuggest($suggest, $page, $limit, $hideIds);

        $remain = $limit - count($suggestModels);

        $models = $this->videoService->getVideos(null, $page, 9, $remain, $hideIds)->toArray();

        return array_merge($suggestModels, $models);
    }

    protected function getHotOrderPerLimit(string $type, int $limit): int
    {
        return (int) match ($type) {
            Video::class => floor($limit * self::HOT_ORDER_VIDEO_PERCENT),
            ImageGroup::class => floor($limit * self::HOT_ORDER_IMAGE_GROUP_PERCENT),
        };
    }

    protected function getUrl(array $model): string
    {
        if ($model['sync_id'] > 0) {
            return env('IMAGE_GROUP_ENCRYPT_URL');
        }

        return $this->getBaseUrl();
    }

    protected function getAdvertisementsLimit(int $page, int $limit): array
    {
        if ($page == 0) {
            return [
                'limit' => 0,
                'last_page' => 0,
            ];
        }

        ++$page;
        $last = floor($limit * ($page - 1) / self::ADVERTISEMENT_PAGE_PER);
        $now = floor($limit * $page / self::ADVERTISEMENT_PAGE_PER);

        if ($now - $last > 0) {
            return [
                'limit' => (int) ($now - $last),
                'last_page' => (int) $last,
            ];
        }

        return [
            'limit' => 0,
            'last_page' => 0,
        ];
    }
}
