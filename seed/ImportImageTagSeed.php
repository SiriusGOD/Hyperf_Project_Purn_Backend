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
class ImportImageTagSeed implements BaseInterface
{
    public function up(): void
    {
        $handle = fopen(BASE_PATH . '/storage/import/import_image_tags.csv', 'r');
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $this->createTags($data);
        }
        fclose($handle);
    }

    public function createActor(array $data): void
    {
        if($data[1] == '尚未分類') {
            return;
        }
        $imageGroup = \App\Model\ImageGroup::where('id', $data[0])->first();
        if (empty($imageGroup)) {
            return;
        }
        $actor = \App\Model\Actor::where('name', $data[1])->first();
        if(empty($actor)) {
            $actor = new \App\Model\Actor();
            $actor->user_id = 0;
            $actor->sex = \App\Model\Actor::SEX['female'];
            $actor->name = $data[1];
            $actor->avatar = '';
            $actor->save();
        }
        $model = new \App\Model\ActorCorrespond();
        $model->correspond_type = \App\Model\ImageGroup::class;
        $model->correspond_id = $imageGroup->id;
        $model->actor_id = $actor->id;
        $model->save();
    }

    public function createTags(array $data): void
    {
        if(empty($data[2])) {
            return;
        }
        $tagNames = explode(',', $data[2]);
        $imageGroup = \App\Model\ImageGroup::where('id', $data[0])->first();
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
        \App\Model\TagCorrespond::where('correspond_type', \App\Model\ImageGroup::class)->delete();
        \App\Model\ActorCorrespond::where('correspond_type', \App\Model\ImageGroup::class)->delete();
    }

    public function base(): bool
    {
        return true;
    }
}
