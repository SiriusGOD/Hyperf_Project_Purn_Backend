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

use App\Model\Advertisement;
use App\Model\ImageGroup;
use App\Model\Video;
use Hyperf\HttpServer\Contract\RequestInterface;

class SearchService extends GenerateService
{
    public const NORMAL_IMAGE_GROUP_PERCENT = 0.5;

    public const NORMAL_VIDEO_PERCENT = 0.5;

    public const ADVERTISEMENT_PAGE_PER = 20;

    public VideoService $videoService;

    public ImageGroupService $imageGroupService;

    public AdvertisementService $advertisementService;

    public string $url;

    public function __construct(
        ImageGroupService $imageGroupService,
        VideoService $videoService,
        AdvertisementService $advertisementService,
        RequestInterface $request
    ) {
        $this->imageGroupService = $imageGroupService;
        $this->videoService = $videoService;
        $this->advertisementService = $advertisementService;
        $this->url = $request->url();
    }

    public function search(?array $tagIds, int $page, int $limit): array
    {
        $imageGroups = $this->imageGroupService->getImageGroups($tagIds, $page, $this->getPerLimit(ImageGroup::class, $limit))->toArray();
        $videos = $this->videoService->getVideos($tagIds, $page, 9, $this->getPerLimit(Video::class, $limit))->toArray();
        $advertisements = $this->advertisementService->getAdvertisementBySearch($page, $this->getPerLimit(Advertisement::class, $limit));

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    public function suggest(array $suggest, int $page, int $limit = 10): array
    {
        $imageGroups = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, $this->getPerLimit(ImageGroup::class, $limit));
        $videos = $this->videoService->getVideosBySuggest($suggest, $page, $this->getPerLimit(Video::class, $limit));
        $advertisements = $this->advertisementService->getAdvertisementBySearch($page, $this->getPerLimit(Advertisement::class, $limit));

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    public function keyword(string $keyword, int $page, int $limit, ?int $sortBy, ?int $isAsc, ?string $filter): array
    {
        switch ($filter) {
            case Video::class:
                $videos = $this->videoService->searchVideo($keyword, 0, 0, $page, $limit, $sortBy, $isAsc)->toArray();
                return $this->generateVideos([], $videos);
            case ImageGroup::class:
                $imageGroups = $this->imageGroupService->getImageGroupsByKeyword($keyword, $page, $limit, $sortBy, $isAsc)->toArray();
                return $this->generateImageGroups([], $imageGroups);
            default:
                $imageGroupLimit = $this->getPerLimit(ImageGroup::class, $limit);
                $imageGroups = $this->imageGroupService->getImageGroupsByKeyword($keyword, $page, $imageGroupLimit, $sortBy, $isAsc)->toArray();
                $videos = $this->videoService->searchVideo($keyword, 0, 0, $page, $limit - $imageGroupLimit, $sortBy, $isAsc)->toArray();
                $result = $this->generateImageGroups([], $imageGroups);
                return $this->generateVideos($result, $videos);
        }
    }

    // TODO 可以做快取去優化，但是需要增加非同步 task 去處理
    public function popular(int $page, int $limit = 10): array
    {
        $advertisements = $this->advertisementService->getAdvertisementBySearch($page, $this->getPerLimit(Advertisement::class, $limit));
        $imageGroups = $this->popularImageGroups($page, $this->getPerLimit(ImageGroup::class, $limit));
        $videos = $this->popularVideos($page, $this->getPerLimit(Video::class, $limit));

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    protected function popularImageGroups(int $page, int $limit = 10): array
    {
        $hotImages = ImageGroup::where('hot_order', '>=', 1)
            ->with(['tags', 'imagesLimit'])
            ->orderBy('hot_order')
            ->where('height', '>', 0)
            ->offset($page * $limit)
            ->limit($limit)
            ->get();

        $remain = $limit - $hotImages->count();
        if ($remain == 0) {
            return $hotImages->toArray();
        }

        $hotImageIds = $hotImages->pluck('id')->toArray();
        $clickService = make(ClickService::class);
        $clicks = $clickService->calculatePopularClick(ImageGroup::class, $remain, $page, $hotImageIds);

        $ids = $this->getIds($clicks);

        $clickImageGroups = ImageGroup::with(['tags', 'imagesLimit'])->whereIn('id', $ids)->get()->toArray();
        $clickImageGroupsArr = $this->sortClickAndModels($clicks, $clickImageGroups);
        $remain = $remain - count($clickImageGroups);
        $result = array_merge($clickImageGroupsArr, $hotImages->toArray());

        if ($remain == 0) {
            return $result;
        }

        $ids = $this->getIds($result);

        $imageGroups = $this->imageGroupService->getImageGroups(null, $page, $remain, $ids)->toArray();

        return array_merge($result, $imageGroups);
    }

    protected function sortClickAndModels(array $clicks, array $models): array
    {
        $result = [];
        foreach ($models as $model) {
            foreach ($clicks as $click) {
                if ($click['id'] == $model['id']) {
                    $model['total'] = $click['total'];
                    $result[] = $model;
                }
            }
        }

        return \Hyperf\Collection\collect($result)->sortByDesc('total')->toArray();
    }

    protected function popularVideos(int $page, int $limit = 10): array
    {
        $hotVideos = Video::with('tags')
            ->where('hot_order', '>=', 1)
            ->orderBy('hot_order')
            ->offset($page * $limit)
            ->limit($limit)
            ->get();

        $remain = $limit - $hotVideos->count();
        if ($remain == 0) {
            return $hotVideos->toArray();
        }

        $hotVideoIds = $hotVideos->pluck('id')->toArray();
        $clickService = make(ClickService::class);
        $clicks = $clickService->calculatePopularClick(Video::class, $remain, $page, $hotVideoIds);

        $ids = $this->getIds($clicks);

        $clickVideos = Video::with('tags')->whereIn('id', $ids)->get()->toArray();
        $clickVideosArr = $this->sortClickAndModels($clicks, $clickVideos);

        $remain = $remain - count($clickVideos);

        $result = array_merge($clickVideosArr, $hotVideos->toArray());

        if ($remain == 0) {
            return $result;
        }

        $videos = $this->videoService->getVideos(null, $page, 9, $remain)->toArray();

        return array_merge($videos, $result);
    }

    protected function getPerLimit(string $type, int $limit): int
    {
        return (int) match ($type) {
            Video::class => floor($limit * self::NORMAL_VIDEO_PERCENT),
            ImageGroup::class => floor($limit * self::NORMAL_IMAGE_GROUP_PERCENT),
            Advertisement::class => floor($limit * self::ADVERTISEMENT_PAGE_PER),
        };
    }
}
