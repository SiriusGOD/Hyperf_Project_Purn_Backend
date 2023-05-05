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

use App\Model\ImageGroup;
use App\Model\MemberCategorizationDetail;
use App\Model\MemberTag;
use App\Model\TagCorrespond;
use App\Model\Video;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class SuggestService
{
    public const MEMBER_TAG_CACHE_KEY = 'member_tag:suggest:';

    public const MEMBER_CATEGORIZATION_CACHE_KEY = 'member_categorization:suggest:';

    public const MIN = 0.01;

    public const LIMIT = 100;

    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 寫入user tag  or update count
    public function storeUserTag(int $tagId, int $userId)
    {
        if (MemberTag::where('tag_id', $tagId)->where('member_id', $userId)->exists()) {
            $model = MemberTag::where('tag_id', $tagId)->where('member_id', $userId)->first();
            $model->count = $model->count + 1;
        } else {
            $model = new MemberTag();
            $model->user_id = $userId;
            $model->tag_id = $tagId;
            $model->count = 1;
        }
        $model->save();
        return $model;
    }

    public function getTagProportionByMemberTag(int $memberId): array
    {
        $result = [];
        $key = self::MEMBER_TAG_CACHE_KEY . $memberId;
        if ($this->redis->exists($key)) {
            return json_decode($this->redis->get($key), true);
        }

        $userTags = MemberTag::where('member_id', $memberId)
            ->limit(self::LIMIT)
            ->orderByDesc('count')
            ->orderBy('id')
            ->get();
        $sum = MemberTag::where('member_id', $memberId)
            ->limit(self::LIMIT)
            ->orderByDesc('count')
            ->orderBy('id')
            ->sum('count');

        foreach ($userTags as $row) {
            $proportion = round($row->count / $sum, 2, PHP_ROUND_HALF_DOWN);

            if ($proportion < self::MIN) {
                break;
            }

            $result[] = [
                'tag_id' => $row->tag_id,
                'proportion' => $proportion,
            ];
        }

        $this->redis->set($key, json_encode($result), 86400);

        return $result;
    }

    public function getTagProportionByMemberCategorization(int $memberCategorizationId): array
    {
        $key = self::MEMBER_CATEGORIZATION_CACHE_KEY . $memberCategorizationId;

        if ($this->redis->exists($key)) {
            return json_decode($this->redis->get($key), true);
        }

        $models = MemberCategorizationDetail::where('member_categorization_id', $memberCategorizationId)
            ->orderByDesc('total_click')
            ->orderBy('id')
            ->limit(self::LIMIT)
            ->get()
            ->toArray();

        $imageGroupTags = $this->getModelTags($models, ImageGroup::class);
        $videoTags = $this->getModelTags($models, Video::class);
        $tags = $this->mergeModelTags($videoTags, $imageGroupTags);
        $sum = \Hyperf\Collection\collect($tags)->sum('total');

        $result = [];
        foreach ($tags as $row) {
            $proportion = round($row['total'] / $sum, 2, PHP_ROUND_HALF_DOWN);

            if ($proportion < self::MIN) {
                break;
            }

            $result[] = [
                'tag_id' => $row['tag_id'],
                'proportion' => $proportion,
            ];
        }

        $this->redis->set($key, json_encode($result), 86400);

        return $result;
    }

    protected function getModelTags(array $memberCategorizationDetails, string $modelType): array
    {
        $ids = [];
        foreach ($memberCategorizationDetails as $detail) {
            if ($detail['type'] == $modelType) {
                $ids[] = $detail['type_id'];
            }
        }
        return TagCorrespond::where('correspond_type', $modelType)
            ->whereIn('correspond_id', $ids)
            ->groupBy('tag_id')
            ->select(Db::raw('count(tag_id) as total'), 'tag_id')
            ->get()
            ->toArray();
    }

    protected function mergeModelTags(array ...$tagArr): array
    {
        $result = [];
        foreach ($tagArr as $item) {
            foreach ($item as $value) {
                if (empty($result[$value['tag_id']])) {
                    $result[$value['tag_id']] = [
                        'tag_id' => $value['tag_id'],
                        'total' => $value['total'],
                    ];
                }

                $result[$value['tag_id']]['total'] += $value['total'];
            }
        }

        $return = [];
        foreach ($result as $row) {
            $return[] = $row;
        }

        return $return;
    }
}
