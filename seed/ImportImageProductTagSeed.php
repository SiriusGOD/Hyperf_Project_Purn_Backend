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
class ImportImageProductTagSeed implements BaseInterface
{
    public function up(): void
    {
        $handle = fopen(BASE_PATH . '/storage/import/import_image_product.csv', 'r');
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $this->createTags($data);
            $this->createActor($data);
            \App\Model\ImageGroup::where('sync_id', $data[0])->update([
                'title' => $data[1],
                'description' => $data[2]
            ]);
        }
        fclose($handle);
    }

    public function createActor(array $data): void
    {
        $imageGroup = \App\Model\ImageGroup::where('sync_id', $data[0])->first();
        if (empty($imageGroup)) {
            return;
        }
        $rows = explode(',', $data[3]);
        \App\Model\ActorCorrespond::where('correspond_type', \App\Model\ImageGroup::class)
            ->where('correspond_id', $imageGroup->id)->delete();
        foreach ($rows as $row) {
            $actor = \App\Model\Actor::where('name', $row)->first();
            if (empty($actor)) {
                continue;
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
        if(empty($data[4])) {
            return;
        }
        $tagNames = explode(',', $data[4]);
        $imageGroup = \App\Model\ImageGroup::where('sync_id', $data[0])->first();
        if (empty($imageGroup)) {
            return;
        }
        \App\Model\TagCorrespond::where('correspond_type', \App\Model\ImageGroup::class)
            ->where('correspond_id', $imageGroup->id)->delete();
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

    public function getTagId(string $name) : int
    {
        $tag = \App\Model\Tag::where('name', $name)->first();
        if (empty($tag)) {
            return 0;
        }

        return $tag->id;
    }

    public function down(): void
    {

    }

    public function base(): bool
    {
        return false;
    }
}
