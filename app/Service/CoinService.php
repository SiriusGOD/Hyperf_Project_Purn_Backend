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

use App\Model\Coin;
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class CoinService
{
    public const CACHE_KEY = 'coin';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }
    public function store(array $params): void
    {
        $model = Coin::where('id', $params['id'])->first();
        if (empty($model)) {
            $model = new Coin();
        }
        $model->user_id = $params['user_id'];
        $model->type = $params['type'];
        $model->name = $params['name'];
        $model->points = $params['points'];
        $model->save();
    }
}
