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

use Hyperf\HttpServer\Contract\RequestInterface;

class SearchService
{
    public const IMAGE_GROUP_PAGE_PER = 60;

    public const VIDEO_PAGE_PER = 30;

    public const ADVERTISEMENT_PAGE_PER = 10;

    public $videoService;

    public $imageGroupService;

    public $advertisementService;

    public string $url;

    public string $baseUrl;

    public function __construct(ImageGroupService $imageGroupService, VideoService $videoService, AdvertisementService $advertisementService, RequestInterface $request)
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
            foreach ($imageGroup['images'] as $key => $image) {
                $imageGroup['images'][$key]['thumbnail'] = $this->baseUrl . $imageGroup['images'][$key]['thumbnail'];
                $imageGroup['images'][$key]['url'] = $this->baseUrl . $imageGroup['images'][$key]['url'];
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
}
