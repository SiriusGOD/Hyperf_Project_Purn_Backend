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
use App\Model\ImageGroup;
use App\Model\Video;

class GenerateService
{
    public function generateImageGroups(array $result, array $imageGroups): array
    {
        foreach ($imageGroups as $imageGroup) {
            $url = $this->getImageUrl($imageGroup);
            $imageGroup['thumbnail'] = $url . $imageGroup['thumbnail'];
            $imageGroup['url'] = $url . $imageGroup['url'];
            if (empty($imageGroup['images_limit'])) {
                $result[] = $imageGroup;
                continue;
            }
            $imageGroup['actors'] = $this->getActorsByType(ImageGroup::class, $imageGroup['id']);
            foreach ($imageGroup['images_limit'] as $key => $image) {
                $imageGroup['images_limit'][$key]['thumbnail'] = $url . $imageGroup['images_limit'][$key]['thumbnail'];
                $imageGroup['images_limit'][$key]['url'] = $url . $imageGroup['images_limit'][$key]['url'];
            }

            $result[] = $imageGroup;
        }

        return $result;
    }

    public function getActorsByType(string $type, int $id): array
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

        if (empty($actors)) {
            return [Constants::DEFAULT_ACTOR];
        }

        $result = [];
        $baseUrl = $this->getBaseUrl();
        foreach ($actors as $actor) {
            if (! empty($actor['avatar'])) {
                $actor['avatar'] = $baseUrl . $actor['avatar'];
            }else{
                $actor['avatar'] = "";
            }
            $result[] = $actor;
        }

        return $result;
    }

    public function generateVideos(array $result, array $videos): array
    {
        foreach ($videos as $video) {
            $video['cover_thumb'] = env('IMAGE_GROUP_ENCRYPT_URL', 'https://new.cnzuqiu.mobi') . $video['cover_thumb'];
            $video['full_m3u8'] = env('VIDEO_SOURCE_URL', 'https://video.iwanna.tv') . $video['full_m3u8'];
            $video['m3u8'] = env('VIDEO_SOURCE_URL', 'https://video.iwanna.tv') . $video['m3u8'];
            $video['source'] = env('VIDEO_SOURCE_URL_10S', 'https://10play.riyufanyi.wang') . $video['source'];
            $video['actors'] = $this->getActorsByType(Video::class, $video['id']);
            unset($video['coins']);

            $result[] = $video;
        }

        return $result;
    }

    public function generateImage(int $id, array $images): array
    {
        $imageGroup = ImageGroup::find($id)->toArray();
        $url = $this->getImageUrl($imageGroup);
        $result = [];
        foreach ($images as $key => $image) {
            $image['thumbnail'] = $url . $image['thumbnail'];
            $image['url'] = $url . $image['url'];
            $result[] = $image;
        }

        return $result;
    }

    protected function getBaseUrl()
    {
        return env('IMAGE_GROUP_ENCRYPT_URL');
    }

    protected function generateAdvertisements(array $result, array $advertisements): array
    {
        foreach ($advertisements as $advertisement) {
            $advertisement['image_url'] = $this->getBaseUrl() . $advertisement['image_url'];
            $result[] = $advertisement;
        }

        return $result;
    }

    protected function getImageUrl(array $model): string
    {
        if ($model['sync_id'] > 0) {
            return env('IMAGE_GROUP_ENCRYPT_URL');
        }

        return $this->getBaseUrl();
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
