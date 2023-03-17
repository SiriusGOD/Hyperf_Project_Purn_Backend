<?php

namespace App\Service;

use App\Model\Tag;
use App\Model\TagCorrespond;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Model\Model;

class TagService
{
    public function getTags() : Collection
    {
        return Tag::all();
    }

    public function createTag(string $name, int $userId) : void
    {
        $model = new Tag();
        $model->name = $name;
        $model->user_id = $userId;
        $model->save();
    }

    public function createTagRelationship(string $className, int $classId, int $tagId) : void
    {
        $model = new TagCorrespond();
        $model->correspond_type = $className;
        $model->correspond_id = $classId;
        $model->tag_id = $tagId;
        $model->save();
    }

    public function createTagRelationshipArr(string $className, int $classId, array $tagIds) : void
    {
        TagCorrespond::where('correspond_type', $className)
            ->where('correspond_id', $classId)
            ->delete();
        foreach ($tagIds as $tagId) {
            $this->createTagRelationship($className, $classId, $tagId);
        }
    }
}