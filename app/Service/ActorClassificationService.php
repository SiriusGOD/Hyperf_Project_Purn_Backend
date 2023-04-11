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

use App\Model\ActorClassification;
use Carbon\Carbon;
use Hyperf\Redis\Redis;

class ActorClassificationService
{
    public const CACHE_KEY = 'actorClassification';

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    // 新增或更新分類
    public function storeActorClassification(array $data): void
    {
        $model = ActorClassification::findOrNew($data['id']);
        $model->sort = $data['sort'];
        $model->user_id = $data['user_id'];
        $model->name = $data['name'];
        $model->save();
    }
}
