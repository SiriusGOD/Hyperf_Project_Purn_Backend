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
use App\Model\Video;
use Hyperf\HttpServer\Contract\RequestInterface;

class SearchService
{
    public const IMAGE_GROUP_PAGE_PER = 60;

    public const VIDEO_PAGE_PER = 30;

    public const ADVERTISEMENT_PAGE_PER = 10;

    public const POPULAR_CACHE_KEY = 'search:popular:';

    public VideoService $videoService;

    public ImageGroupService $imageGroupService;

    public AdvertisementService $advertisementService;

    public string $url;

    public string $baseUrl;

    public function __construct(
        ImageGroupService $imageGroupService,
        VideoService $videoService,
        AdvertisementService $advertisementService,
        RequestInterface $request
    )
    {
        $this->imageGroupService = $imageGroupService;
        $this->videoService = $videoService;
        $this->advertisementService = $advertisementService;
        $this->url = $request->url();
        $this->baseUrl = $this->getBaseUrl();
    }

    public function search(?array $tagIds, int $page): array
    {
        $imageGroups = $this->imageGroupService->getImageGroups($tagIds, $page, self::IMAGE_GROUP_PAGE_PER)->toArray();
        $videos = $this->videoService->getVideos($tagIds, $page, 9, self::VIDEO_PAGE_PER)->toArray();
        $advertisements = $this->advertisementService->getAdvertisementBySearch($page, self::ADVERTISEMENT_PAGE_PER);

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    public function suggest(array $suggest, int $page): array
    {
        $imageGroups = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, self::IMAGE_GROUP_PAGE_PER);
        $videos = $this->videoService->getVideosBySuggest($suggest, $page, self::VIDEO_PAGE_PER);
        $advertisements = $this->advertisementService->getAdvertisementBySearch($page, self::ADVERTISEMENT_PAGE_PER);

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    public function keyword(string $keyword, int $page): array
    {
        $imageGroups = $this->imageGroupService->getImageGroupsByKeyword($keyword, $page, self::IMAGE_GROUP_PAGE_PER)->toArray();
        $videos = $this->videoService->searchVideo($keyword, 0, 0, self::VIDEO_PAGE_PER)->toArray();
        $advertisements = $this->advertisementService->getAdvertisementBySearch($page, self::ADVERTISEMENT_PAGE_PER);

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    // TODO 可以做快取去優化，但是需要增加非同步 task 去處理
    public function popular(int $page): array
    {
        $advertisements = $this->advertisementService->getAdvertisementBySearch($page, self::ADVERTISEMENT_PAGE_PER);
        $imageGroups = $this->popularImageGroups($page);
        $videos = $this->popularVideos($page);

        $result = [];

        $result = $this->generateImageGroups($result, $imageGroups);
        $result = $this->generateVideos($result, $videos);
        return $this->generateAdvertisements($result, $advertisements);
    }

    protected function generateAdvertisements(array $result, array $advertisements): array
    {
        foreach ($advertisements as $advertisement) {
            $advertisement['image_url'] = $this->baseUrl . $advertisement['image_url'];
            $result[] = $advertisement;
        }

        return $result;
    }

    protected function getBaseUrl()
    {
        $urlArr = parse_url($this->url);
        $port = $urlArr['port'] ?? '80';

        if ($urlArr['scheme'] == 'https' and empty($urlArr['port'])) {
            $port = 443;
        }

        return $urlArr['scheme'] . '://' . $urlArr['host'] . ':' . $port;
    }

    protected function generateImageGroups(array $result, array $imageGroups): array
    {
        foreach ($imageGroups as $imageGroup) {
            $imageGroup['thumbnail'] = $this->baseUrl . $imageGroup['thumbnail'];
            $imageGroup['url'] = $this->baseUrl . $imageGroup['url'];
            foreach ($imageGroup['images_limit'] as $key => $image) {
                $imageGroup['images_limit'][$key]['thumbnail'] = $this->baseUrl . $imageGroup['images_limit'][$key]['thumbnail'];
                $imageGroup['images_limit'][$key]['url'] = $this->baseUrl . $imageGroup['images_limit'][$key]['url'];
            }

            $result[] = $imageGroup;
        }

        return $result;
    }

    protected function generateVideos(array $result, array $videos): array
    {
        foreach ($videos as $video) {
            $video['cover_thumb'] = $this->baseUrl . $video['cover_thumb'];
            $video['full_m3u8'] = $this->baseUrl . $video['full_m3u8'];
            $video['m3u8'] = $this->baseUrl . $video['m3u8'];

            $result[] = $video;
        }

        return $result;
    }

    protected function popularImageGroups(int $page): array
    {
        $hotImages = ImageGroup::where('hot_order', '>=', 1)
            ->with(['tags', 'imagesLimit'])
            ->orderBy('hot_order')
            ->offset($page * self::IMAGE_GROUP_PAGE_PER)
            ->limit(self::IMAGE_GROUP_PAGE_PER)
            ->get();

        $remain = self::IMAGE_GROUP_PAGE_PER - $hotImages->count();
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

    protected function popularVideos(int $page): array
    {
        $hotVideos = Video::with('tags')
            ->where('hot_order', '>=', 1)
            ->orderBy('hot_order')
            ->offset($page * self::IMAGE_GROUP_PAGE_PER)
            ->limit(self::IMAGE_GROUP_PAGE_PER)
            ->get();

        $remain = self::VIDEO_PAGE_PER - $hotVideos->count();
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

    protected function getIds(array $models): array
    {
        $result = [];

        foreach ($models as $model) {
            $result[] = $model['id'];
        }

        return $result;
    }
}
