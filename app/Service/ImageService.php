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

use App\Model\Image;
use App\Model\Tag;
use App\Model\TagCorrespond;
use Carbon\Carbon;
use Hyperf\Database\Model\Collection;
use Intervention\Image\ImageManager;

class ImageService
{
    public function storeImage(array $data): Image
    {
        $model = Image::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->title = $data['title'];
        if (! empty($data['url'])) {
            $model->thumbnail = $data['thumbnail'];
            $model->url = $data['url'];
        }
        $model->like = $model->like ?? 0;
        $model->group_id = $data['group_id'];
        $model->description = $data['description'];
        $model->save();

        return $model;
    }

    public function moveImageFile($file): array
    {
        $extension = $file->getExtension();
        $filename = sha1(Carbon::now()->toDateTimeString());
        if (! file_exists(BASE_PATH . '/public/image')) {
            mkdir(BASE_PATH . '/public/image', 0755);
        }
        $imageUrl = '/image/' . $filename . '.' . $extension;
        $path = BASE_PATH . '/public' . $imageUrl;
        $file->moveTo($path);

        return [
            'url' => $imageUrl,
            'path' => $path,
        ];
    }

    public function createThumbnail(string $filePath): string
    {
        $pathInfo = pathinfo($filePath);
        $manager = new ImageManager();
        $image = $manager->make($filePath)->resize(300, 300);
        $imageUrl = '/image/' . $pathInfo['filename'] . '_thumbnail.' . $pathInfo['extension'];
        $image->save(BASE_PATH . '/public' . $imageUrl);

        return $imageUrl;
    }

    public function getImages(?array $tagIds, int $page): Collection
    {
        $imageIds = [];
        if (! empty($tagIds)) {
            $imageIds = TagCorrespond::where('correspond_type', Image::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id');
        }

        $query = Image::with([
            'tags',
        ])
            ->offset(Image::PAGE_PER * $page)
            ->limit(Image::PAGE_PER);

        if (! empty($imageIds)) {
            $query = $query->whereIn('id', $imageIds);
        }

        return $query->get();
    }

    public function getImagesByKeyword(string $keyword, int $page): Collection
    {
        $tagIds = Tag::where('name', 'like', '%' . $keyword . '%')->get()->pluck('id')->toArray();
        $imageIds = [];
        if (! empty($tagIds)) {
            $imageIds = TagCorrespond::where('correspond_type', Image::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id');
        }

        $query = Image::with([
            'tags',
        ])
            ->orWhere('title', 'like', '%' . $keyword . '%')
            ->offset(Image::PAGE_PER * $page)
            ->limit(Image::PAGE_PER);

        if (! empty($imageIds)) {
            $query = $query->orWhereIn('id', $imageIds);
        }

        return $query->get();
    }

    public function getImagesBySuggest(array $suggest, int $page): array
    {
        $result = [];
        $useImageIds = [];
        foreach ($suggest as $value) {
            $limit = $value['proportion'] * Image::PAGE_PER;
            if ($limit < 1) {
                break;
            }

            $imageIds = TagCorrespond::where('correspond_type', Image::class)
                ->where('tag_id', $value['tag_id'])
                ->whereNotIn('correspond_id', $useImageIds)
                ->get()
                ->pluck('correspond_id')
                ->toArray();

            $useImageIds = array_unique(array_merge($imageIds, $useImageIds));

            $models = Image::with([
                'tags',
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
}
