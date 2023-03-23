<?php

namespace App\Service;

use App\Model\UserTag;
use Hyperf\Redis\Redis;

class SuggestService
{
    private Redis $redis;
    public const CACHE_KEY = 'user:suggest:';

    public const MIN = 0.01;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }
    public function getTagProportionByUser(int $userId) : array
    {
        $result = [];
        $key = self::CACHE_KEY . $userId;
        if($this->redis->exists($key)) {
            return json_decode($this->redis->get($key), true);
        }

        $userTags = UserTag::where('user_id', $userId)->orderByDesc('count')->get();
        $sum = UserTag::where('user_id', $userId)->sum('count');

        foreach ($userTags as $row) {
            $proportion = round($row->count / $sum, 2, PHP_ROUND_HALF_DOWN);

            if ($proportion < self::MIN) {
                break;
            }

            $result[] = [
                'tag_id' => $row->tag_id,
                'proportion' => $proportion
            ];

        }

        $this->redis->set($key, json_encode($result), 86400);

        return $result;
    }
}