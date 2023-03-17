<?php

namespace App\Service;

use App\Model\Image;
use App\Model\TagCorrespond;
use Carbon\Carbon;
use Intervention\Image\ImageManager;

class ImageService
{
    public function storeImage(array $data) : Image
    {
        $model = Image::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->title = $data['title'];
        if (!empty($data['url'])) {
            $model->thumbnail = $data['thumbnail'];
            $model->url = $data['url'];
        }
        $model->likes = $model->likes ?? 0;
        $model->group_id = $data['group_id'];
        $model->description = $data['description'];
        $model->save();

        return $model;
    }

    public function moveImageFile($file) : array
    {
        $extension = $file->getExtension();
        $filename = sha1(Carbon::now()->toDateTimeString());
        if(!file_exists(BASE_PATH.'/public/image')){
            mkdir(BASE_PATH.'/public/image', 0755);
        }
        $imageUrl = '/image/' . $filename . '.' . $extension;
        $path = BASE_PATH . '/public' . $imageUrl;
        $file->moveTo($path);

        return [
            'url' => $imageUrl,
            'path' => $path
        ];
    }

    public function createThumbnail(string $filePath) : string
    {
        $pathInfo = pathinfo($filePath);
        $manager = new ImageManager();
        $image = $manager->make($filePath)->resize(300, 300);
        $imageUrl = '/image/' . $pathInfo['filename'] . '_thumbnail.' . $pathInfo['extension'];
        $image->save(BASE_PATH . '/public' . $imageUrl);

        return $imageUrl;
    }

    public function getImages(?array $tagIds)
    {
        $imageIds = [];
        if (!empty($tagIds)) {
            $imageIds = TagCorrespond::where('correspond_type', Image::class)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->pluck('correspond_id');
        }

        $query = Image::with([
            'tags'
        ]);

        if (!empty($imageIds)) {
            $query = $query->whereIn('id', $imageIds);
        }

        return $query->get();
    }
}