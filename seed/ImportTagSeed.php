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
class ImportTagSeed implements BaseInterface
{
    public function up(): void
    {
        $handle = fopen(BASE_PATH . '/storage/import/import_tags.csv', 'r');
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $this->createImportTag($data);
        }
        fclose($handle);
    }

    public function createImportTag(array $data): void
    {
        $id = $this->getTagId($data);
        $model = \App\Model\ImportTag::where('name', $data[0])->first();
        if(empty($model)) {
            $model = new \App\Model\ImportTag;
            $model->name = $data[0];
            $model->tag_id = $id;
            $model->save();
        }
    }

    public function getTagId(array $data) : int
    {
        $groupId = $this->getTagGroupId($data[2]);
        $model = \App\Model\Tag::where('name', $data[1])->first();
        if(empty($model)) {
            $model = new \App\Model\Tag();
            $model->user_id = 0;
            $model->name = $data[1];
            $model->save();
        }

        $exist = \App\Model\TagHasGroup::where('tag_id', $model->id)
            ->where('tag_group_id', $groupId)
            ->exists();

        if (! $exist) {
            $mapModal = new \App\Model\TagHasGroup;
            $mapModal->tag_id = $model->id;
            $mapModal->tag_group_id = $groupId;
            $mapModal->save();
        }

        return $model->id;
    }

    public function getTagGroupId(string $name) : int
    {
        $model = \App\Model\TagGroup::where('name', $name)->first();
        if (empty($model)) {
            $model = new \App\Model\TagGroup();
            $model->user_id = 0;
            $model->name = $name;
            $model->save();
        }

        return $model->id;
    }

    public function down(): void
    {
        \App\Model\TagGroup::truncate();
        \App\Model\Tag::truncate();
        \App\Model\TagHasGroup::truncate();
        \App\Model\ImportTag::truncate();
        \App\Model\MemberTag::truncate();
    }

    public function base(): bool
    {
        return true;
    }
}
