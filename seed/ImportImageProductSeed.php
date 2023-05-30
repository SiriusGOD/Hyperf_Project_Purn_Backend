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
use App\Model\Image;
use App\Model\ImageGroup;
use App\Model\Product;

/**
 * This file is part of Hyperf.
 *
 * @see     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class ImportImageProductSeed implements BaseInterface
{
    public function up(): void
    {
        $handle = fopen(BASE_PATH . '/storage/import/import_image_product.csv', 'r');
        $url = env('IMAGE_GROUP_SYNC_URL');
        $client = new \GuzzleHttp\Client();
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if (!empty(env('START_NUM_IMAGE_SYNC')) and $data[0] < (int) env('START_NUM_IMAGE_SYNC')) {
                continue;
            }
            $url = $url . '&_n=' . $data[0];
            try {
                $res = $client->get($url);
            } catch (\Exception $exception) {
                var_dump('套圖取得錯誤 id : ' . $data[0]);
                continue;
            }
            $result = json_decode($res->getBody()->getContents(), true);
            if (empty($result['data'])) {
                var_dump('套圖解析錯誤 id : ' . $data[0]);
                continue;
            }

            $id = $this->createImageGroup($data, $result['data']['thumb']);
            $this->createImages($result['data']['resources'], $id);
            $this->createActor($data);
            $this->createTags($data);
            $this->createProductGroup([
                'id' => $id,
                'title' => $data[1],
            ]);
            var_dump('同步完成 id : ' . $data[0]);
        }
        fclose($handle);
    }

    public function createActor(array $data): void
    {
        if ($data[3] == '尚未分類') {
            return;
        }
        $imageGroup = \App\Model\ImageGroup::where('sync_id', $data[0])->first();
        if (empty($imageGroup)) {
            return;
        }
        $rows = explode(',', $data[3]);
        foreach ($rows as $row) {
            $actor = \App\Model\Actor::where('name', $row)->first();
            if (empty($actor)) {
                $actor = new \App\Model\Actor();
                $actor->user_id = 0;
                $actor->sex = \App\Model\Actor::SEX['female'];
                $actor->name = $row;
                $actor->avatar = '';
                $actor->save();
            }
            $model = new \App\Model\ActorCorrespond();
            $model->correspond_type = \App\Model\ImageGroup::class;
            $model->correspond_id = $imageGroup->id;
            $model->actor_id = $actor->id;
            $model->save();
        }
    }

    public function createTags(array $data): void
    {
        if (empty($data[4])) {
            return;
        }
        $tagNames = explode(',', $data[4]);
        $imageGroup = \App\Model\ImageGroup::where('sync_id', $data[0])->first();
        if (empty($imageGroup)) {
            return;
        }
        foreach ($tagNames as $name) {
            $tagId = $this->getTagId($name);
            if ($tagId == 0) {
                continue;
            }
            $model = new \App\Model\TagCorrespond();
            $model->tag_id = $tagId;
            $model->correspond_type = \App\Model\ImageGroup::class;
            $model->correspond_id = $imageGroup->id;
            $model->save();
        }
    }

    public function getTagId(string $name): int
    {
        $tag = \App\Model\Tag::where('name', $name)->first();
        if (empty($tag)) {
            return 0;
        }

        return $tag->id;
    }

    public function down(): void
    {
        $num = env('START_NUM_IMAGE_SYNC', PHP_INT_MAX);
        \App\Model\TagCorrespond::where('correspond_type', \App\Model\ImageGroup::class)->where('correspond_id', '>=', $num)->delete();
        \App\Model\ActorCorrespond::where('correspond_type', \App\Model\ImageGroup::class)->where('correspond_id', '>=', $num)->delete();
        \App\Model\Image::where('sync_id', '>=', $num)->delete();
        \App\Model\ImageGroup::where('sync_id', '>=', $num)->delete();
        Product::where('type', \App\Model\ImageGroup::class)->where('correspond_id', $num)->delete();
    }

    public function base(): bool
    {
        return false;
    }

    protected function createImageGroup(array $data, string $thumb): ?int
    {
        $url = env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live');
//        $imageInfo = getimagesize($url . $thumb);
//        if ($imageInfo === false) {
//            return null;
//        }
        $model = new ImageGroup();
        $model->user_id = 0;
        $model->title = $data[1];
        $model->thumbnail = $thumb;
        $model->url = $thumb;
        $model->description = $data[2];
        $model->sync_id = $data[0];
        $model->height = $imageInfo[1] ?? 0;
        $model->weight = $imageInfo[0] ?? 0;

        $model->save();

        return $model->id;
    }

    protected function createImages(array $images, int $imageGroupId): void
    {
        foreach ($images as $image) {
            $this->createImage($image, $imageGroupId);
        }
    }

    protected function createImage(array $image, int $imageGroupId): void
    {
        $url = env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live');
//        $imageInfo = getimagesize($url . $image['img_url']);
//        if ($imageInfo === false) {
//            return;
//        }
        $model = new Image();
        $model->user_id = 0;
        $model->title = '';
        $model->thumbnail = $image['img_url'];
        $model->url = $image['img_url'];
        $model->description = '';
        $model->group_id = $imageGroupId;
        $model->sync_id = $image['id'];
        $model->thumbnail_height = $imageInfo[1] ?? 0;
        $model->thumbnail_weight = $imageInfo[0] ?? 0;
        $model->height = $imageInfo[1] ?? 0;
        $model->weight = $imageInfo[0] ?? 0;
        $model->save();
    }

    protected function createProductGroup(array $params) : void
    {
        $data['id'] = null;
        $data['type'] = Product::TYPE_CORRESPOND_LIST['image'];
        $data['correspond_id'] = $params['id'];
        $data['name'] = $params['title'];
        $data['user_id'] = 1;
        $data['expire'] = 0;
        $data['start_time'] = date('Y-m-d H:i:s');
        $data['end_time'] = date('Y-m-d H:i:s', strtotime('+10 years'));
        $data['currency'] = 'COIN';
        $data['diamond_price'] = 1;
        $data['selling_price'] = 0;
        \Hyperf\Support\make(\App\Service\ProductService::class)->store($data);
    }
}
