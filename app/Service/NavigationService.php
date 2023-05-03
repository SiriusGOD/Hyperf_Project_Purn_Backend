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
use App\Model\Image;
use App\Model\ImageGroup;
use App\Model\Navigation;
use App\Model\Video;
use Hyperf\HttpServer\Contract\RequestInterface;

class NavigationService
{
    public const HOT_ORDER_IMAGE_GROUP_PERCENT = 0.1;

    public const HOT_ORDER_VIDEO_PERCENT = 0.1;

    public const ADVERTISEMENT_PAGE_PER = 20;

    public const OTHER_LIMIT = 1;

    public const POPULAR_CACHE_KEY = 'search:popular:';

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

        switch ($navId) {
            case 1:
                $ids = $this->getIds($videos);
                $clicks = $this->calculateNavigationPopularClick(Video::class, $ids);
                $videos = $this->sortClickAndModels($clicks, $videos);
                $ids = $this->getIds($imageGroups);
                $clicks = $this->calculateNavigationPopularClick(ImageGroup::class, $ids);
                $imageGroups = $this->sortClickAndModels($clicks, $videos);
                return array_merge($imageGroups, $videos);
            default:
                $result = array_merge($imageGroups, $videos);
                $collect = \Hyperf\Collection\collect($result);
                $collect = $collect->sortByDesc('created_at');

                return $collect->toArray();
        }
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

    public function navigationPopular(array $suggest, int $page, int $limit): array
    {
        $advertisementLimitArr = $this->getAdvertisementsLimit($page, $limit);
        if ($advertisementLimitArr['limit'] > 0) {
            --$limit;
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

        return $collect->toArray();
    }

    protected function navigationDetailImageGroups(array $suggest, int $navId, array $tagIds, int $page, int $limit): array
    {
        $otherLimit = 0;
        $percent = self::DETAIL_PERCENTS[$navId];
        $typeLimit = (int) floor($percent[1] * $limit);

        $imageGroupIds = $this->tagService->getTypeIdsByTagIds($tagIds, ImageGroup::class, $page, $typeLimit);
        $imageGroups = ImageGroup::with([
            'tags', 'imagesLimit',
        ])
            ->whereIn('id', $imageGroupIds)
            ->offset($limit * $page)
            ->limit($limit)
            ->get()
            ->toArray();

        $userLimit = $limit - count($imageGroups);

        $suggestModels = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, $userLimit);
        $remain = $userLimit - count($suggestModels);
        if ($remain >= 1) {
            $otherLimit += $remain;
        }

        $models = $this->imageGroupService->getImageGroups(null, $page, $otherLimit)->toArray();

        return array_merge($models, $suggestModels, $imageGroups);
    }

    protected function navigationDetailVideos(array $suggest, int $navId, array $tagIds, int $page, int $limit): array
    {
        $otherLimit = 0;
        $percent = self::DETAIL_PERCENTS[$navId];
        $typeLimit = (int) floor($percent[1] * $limit);

        $ids = $this->tagService->getTypeIdsByTagIds($tagIds, Video::class, $page, $typeLimit);
        $videos = Video::with([
            'tags',
        ])
            ->whereIn('id', $ids)
            ->offset($limit * $page)
            ->limit($limit)
            ->get()
            ->toArray();

        $userLimit = $limit - count($videos);

        $suggestModels = $this->videoService->getVideosBySuggest($suggest, $page, $userLimit);
        $remain = $userLimit - count($suggestModels);
        if ($remain >= 1) {
            $otherLimit += $remain;
        }

        $models = $this->videoService->getVideos(null, $page, 9, $otherLimit)->toArray();

        return array_merge($models, $suggestModels, $videos);
    }

    protected function navigationPopularImageGroups(array $suggest, int $page, int $limit): array
    {
        $otherLimit = self::OTHER_LIMIT;
        $suggestLimit = $limit - $otherLimit;
        $suggestModels = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, $suggestLimit);
        $remain = $suggestLimit - count($suggestModels);
        if ($remain >= 1) {
            $otherLimit += $remain;
        }

        $models = $this->imageGroupService->getImageGroups(null, $page, $otherLimit)->toArray();

        $result = array_merge($suggestModels, $models);
        $ids = $this->getIds($result);
        $clicks = $this->calculateNavigationPopularClick(Image::class, $ids);
        return $this->sortClickAndModels($clicks, $result);
    }

    protected function navigationPopularVideos(array $suggest, int $page, int $limit): array
    {
        $otherLimit = self::OTHER_LIMIT;
        $suggestLimit = $limit - $otherLimit;
        $suggestModels = $this->videoService->getVideosBySuggest($suggest, $page, $suggestLimit);
        $remain = $suggestLimit - count($suggestModels);
        if ($remain >= 1) {
            $otherLimit += $remain;
        }

        $models = $this->videoService->getVideos(null, $page, 9, $otherLimit)->toArray();

        $result = array_merge($suggestModels, $models);
        $ids = $this->getIds($result);
        $clicks = $this->calculateNavigationPopularClick(Video::class, $ids);
        return $this->sortClickAndModels($clicks, $result);
    }

    protected function calculateNavigationPopularClick(string $type, array $ids): array
    {
        $clickService = make(ClickService::class);
        return $clickService->calculatePopularClickByTypeIds($type, $ids);
    }

    protected function navigationSuggestImageGroups(array $suggest, int $page, int $limit): array
    {
        $hotOrderLimit = $this->getHotOrderPerLimit(ImageGroup::class, $limit);
        $hotOrderModels = $this->imageGroupService->getImageGroupsByHotOrder($page, $hotOrderLimit);
        $otherLimit = self::OTHER_LIMIT;
        $suggestLimit = $limit - $hotOrderLimit - $otherLimit;
        if ($suggestLimit <= 0) {
            return $hotOrderModels;
        }
        $suggestModels = $this->imageGroupService->getImageGroupsBySuggest($suggest, $page, $suggestLimit);
        $remain = $suggestLimit - count($suggestModels);
        if ($remain >= 1) {
            $otherLimit += $remain;
        }

        $models = $this->imageGroupService->getImageGroups(null, $page, $otherLimit)->toArray();

        return array_merge($hotOrderModels, $suggestModels, $models);
    }

    protected function navigationSuggestVideos(array $suggest, int $page, int $limit): array
    {
        $hotOrderLimit = $this->getHotOrderPerLimit(Video::class, $limit);
        $hotOrderModels = $this->videoService->getVideosByHotOrder($page, $hotOrderLimit);
        $otherLimit = self::OTHER_LIMIT;
        $suggestLimit = $limit - $hotOrderLimit - $otherLimit;
        if ($suggestLimit <= 0) {
            return $hotOrderModels;
        }

        $suggestModels = $this->videoService->getVideosBySuggest($suggest, $page, $limit);

        $remain = $suggestLimit - count($suggestModels);
        if ($remain >= 1) {
            $otherLimit += $remain;
        }

        $models = $this->videoService->getVideos(null, $page, $otherLimit)->toArray();

        return array_merge($hotOrderModels, $suggestModels, $models);
    }

    protected function generateAdvertisements(array $result, array $advertisements): array
    {
        foreach ($advertisements as $advertisement) {
            $advertisement['image_url'] = $this->getBaseUrl() . $advertisement['image_url'];
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

        $result = $urlArr['scheme'] . '://' . $urlArr['host'] . ':' . $port;
        if (! empty(env('TEST_IMG_URL'))) {
            return env('TEST_IMG_URL');
        }

        return $result;
    }

    protected function generateImageGroups(array $result, array $imageGroups): array
    {
        foreach ($imageGroups as $imageGroup) {
            $url = $this->getUrl($imageGroup);
            $imageGroup['thumbnail'] = $url . $imageGroup['thumbnail'];
            $imageGroup['url'] = $url . $imageGroup['url'];
            $count = 0;
            $imageGroup['actors'] = $this->getActors('image_group', $imageGroup['id']);
            foreach ($imageGroup['images_limit'] as $key => $image) {
                if ($count >= ImageGroup::DEFAULT_FREE_LIMIT) {
                    unset($imageGroup['images_limit'][$key]);
                    continue;
                }
                $imageGroup['images_limit'][$key]['thumbnail'] = $url . $imageGroup['images_limit'][$key]['thumbnail'];
                $imageGroup['images_limit'][$key]['url'] = $url . $imageGroup['images_limit'][$key]['url'];
                $count++;
            }

            $result[] = $imageGroup;
        }

        return $result;
    }

    protected function generateVideos(array $result, array $videos): array
    {
        foreach ($videos as $video) {
            $video['cover_thumb'] = env('VIDEO_THUMB_URL', 'https://new.cnzuqiu.mobi') . $video['cover_thumb'];
            $video['full_m3u8'] = env('VIDEO_SOURCE_URL', 'https://video.iwanna.tv') . $video['full_m3u8'];
            $video['m3u8'] = env('VIDEO_SOURCE_URL', 'https://video.iwanna.tv') . $video['m3u8'];
            $video['source'] = env('VIDEO_SOURCE_URL', 'https://video.iwanna.tv') . $video['source'];
            $video['actors'] = $this->getActors('video', $video['id']);

            $result[] = $video;
        }

        return $result;
    }

    protected function sortClickAndModels(array $clicks, array $models): array
    {
        $result = [];
        $ids = [];
        foreach ($models as $model) {
            foreach ($clicks as $click) {
                if ($click['id'] == $model['id']) {
                    $model['total'] = $click['total'];
                    $result[] = $model;
                    $ids[$model['id']] = true;
                }
            }
        }

        foreach ($models as $model) {
            if (! empty($ids[$model['id']])) {
                continue;
            }

            $model['total'] = 0;
            $result[] = $model;
        }

        return \Hyperf\Collection\collect($result)->sortByDesc('total')->toArray();
    }

    protected function getIds(array $models): array
    {
        $result = [];

        foreach ($models as $model) {
            $result[] = $model['id'];
        }

        return $result;
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

        $page++;
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

    public function createNavigation(array $params) : void
    {
        $model = Navigation::where('id', $params['id'])->first();
        if (empty($model)) {
            $model = new Navigation();
        }

        $model->name = $params['name'];
        $model->hot_order = $params['hot_order'];
        $model->save();
    }

    public function getActors(string $type, int $id) : array
    {
        $actorIds = ActorCorrespond::where('correspond_type', $type)
            ->where('correspond_id', $id)
            ->get()
            ->pluck('actor_id')
            ->toArray();

        if (empty($actorIds)) {
            return [Constants::DEFAULT_ACTOR];
        }

        $actors = Actor::whereIn('id', $actorIds)->get()->toArray();

        $result = [];
        $baseUrl = $this->getBaseUrl();
        foreach ($actors as $actor) {
            if (! empty($actor['avatar'])) {
                $actor['avatar'] = $baseUrl . $actor['avatar'];
            }
            $result[] = $actor;
        }

        return $result;
    }
}
