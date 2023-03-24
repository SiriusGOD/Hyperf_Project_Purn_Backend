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

use App\Model\Tag;
use App\Model\TagCorrespond;
use Hyperf\Database\Model\Collection;

class TagService
{
    public function getTags(): Collection
    {
        return Tag::all();
    }

    public function createTag(string $name, int $userId): void
    {
        $model = new Tag();
        $model->name = $name;
        $model->user_id = $userId;
        $model->save();
    }

    // 影片TAG關聯
    public function videoCorrespondTag(array $data, int $videoId)
    {
        if ($data['tags'] != '') {
            $tags = explode(',', $data['tags']);
            foreach ($tags as $v) {
                if (strlen($v) > 1) {
                    $tag = self::createTagByName($v, 1);
                    self::createTagRelationship('video', $videoId, $tag->id);
                }
            }
        }
    }

    // 新增時以name為主 有重覆不新增
    public function createTagByName(string $name, int $userId)
    {
        if (Tag::where('name', $name)->exists()) {
            $model = Tag::where('name', $name)->first();
        } else {
            $model = new Tag();
        }
        $model->name = $name;
        $model->user_id = $userId;
        $model->save();
        return $model;
    }

    public function createTagRelationship(string $className, int $classId, int $tagId): void
    {
        $model = new TagCorrespond();
        $model->correspond_type = $className;
        $model->correspond_id = $classId;
        $model->tag_id = $tagId;
        $model->save();
    }

    public function createTagRelationshipArr(string $className, int $classId, array $tagIds): void
    {
        TagCorrespond::where('correspond_type', $className)
            ->where('correspond_id', $classId)
            ->delete();
        foreach ($tagIds as $tagId) {
            $this->createTagRelationship($className, $classId, $tagId);
        }
    }
}
