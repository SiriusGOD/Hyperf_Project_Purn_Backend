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

use App\Model\TagGroup;
use Hyperf\Database\Model\Collection;
use Hyperf\Redis\Redis;

class TagGroupService
{
    public const POPULAR_TAG_CACHE_KEY = 'popular_tag';

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function getTags(): Collection
    {
        return TagGroup::all();
    }

    public function storeTagGroup(array $data): void
    {
        $model = TagGroup::findOrNew($data['id']);
        $model->name = $data['name'];
        $model->user_id = $data['user_id'];
        $model->is_hide = $data['is_hide'];
        $model->save();
        // $this->updateCache();
    }
}
