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

use App\Model\MemberTag;
use App\Model\Tag;
use App\Model\TagCorrespond;
use App\Model\TagHasGroup;
use Hyperf\Database\Model\Collection;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class TagService
{
    public const POPULAR_TAG_CACHE_KEY = 'popular_tag';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function getTags(): Collection
    {
        return Tag::all();
    }

    public function createTag(string $name, int $userId, array $groups): void
    {
        $model = new Tag();
        $model->name = $name;
        $model->user_id = $userId;
        $model->save();

        if (count($groups) > 0) {
            $id = $model->id;
            $this->createTagGroupRelationship($groups, $id);
        }
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

    public function getPopularTag()
    {
        if ($this->redis->exists(self::POPULAR_TAG_CACHE_KEY)) {
            return json_decode($this->redis->get(self::POPULAR_TAG_CACHE_KEY), true);
        }

        $collect = $this->calculatePopularTag();

        return $collect->toArray();
    }

    public function calculatePopularTag()
    {
        $models = MemberTag::groupBy('tag_id')
            ->select('tag_id', Db::raw('sum(count) as total'), 'tags.name')
            ->join('tags', 'tags.id', '=', 'member_tags.tag_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        if (! empty($models)) {
            $this->redis->set(self::POPULAR_TAG_CACHE_KEY, $models->toJson());
        }

        return $models;
    }

    public static function tagIdsToInt(?array $tags): array
    {
        $result = [];

        if (empty($tags)) {
            return $result;
        }

        foreach ($tags as $tag) {
            $result[] = (int) $tag;
        }

        return $result;
    }

    // 新增或更新標籤群組關係
    public function createTagGroupRelationship(array $groups, int $tag_id)
    {
        TagHasGroup::where('tag_id', $tag_id)->delete();
        foreach ($groups as $key => $value) {
            $model = TagHasGroup::where('tag_group_id', $value)
                ->where('tag_id', $tag_id);
            if (! $model->exists()) {
                $model = new TagHasGroup();
                $model->tag_id = $tag_id;
                $model->tag_group_id = $value;
                $model->save();
            }
        }
    }
}
