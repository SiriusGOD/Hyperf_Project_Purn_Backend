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
use Hyperf\Redis\Redis;

class SuggestService
{
    public const CACHE_KEY = 'user:suggest:';

    public const MIN = 0.01;

    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }
    //寫入user tag  or update count
    public function storeUserTag(int $tagId, int $userId)
    {
        if (MemberTag::where('tag_id', $tagId)->where('user_id', $userId)->exists()) {
            $model = MemberTag::where('tag_id', $tagId)->where('user_id', $userId)->first();
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
    public function getTagProportionByUser(int $userId): array
    {
        $result = [];
        $key = self::CACHE_KEY . $userId;
        if ($this->redis->exists($key)) {
            return json_decode($this->redis->get($key), true);
        }

        $userTags = MemberTag::where('user_id', $userId)->orderByDesc('count')->get();
        $sum = MemberTag::where('user_id', $userId)->sum('count');

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
}
