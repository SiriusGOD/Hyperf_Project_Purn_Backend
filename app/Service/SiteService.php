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

use App\Model\Site;
use App\Traits\SitePermissionTrait;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Collection;

class SiteService
{
    use SitePermissionTrait;

    public const CACHE_KEY = 'site';

    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function storeSite(?int $id, string $url, string $name): void
    {
        $model = Site::findOrNew($id);
        $model->url = $url;
        $model->name = $name;
        $model->save();
        $this->updateCache();
    }

    public function updateCache(): void
    {
        $models = Site::whereNull('deleted_at')->get();
        $this->redis->set(self::CACHE_KEY, $models->toJson());
    }

    public function getSites(): array
    {
        if ($this->redis->exists(self::CACHE_KEY)) {
            return json_decode($this->redis->get(self::CACHE_KEY), true);
        }

        $models = Site::whereNull('deleted_at')->get();
        $this->redis->set(self::CACHE_KEY, $models->toJson());

        return $models->toArray();
    }

    public function getAdminSelectSites(): Collection
    {
        $query = Site::whereNull('deleted_at');
        $query = $this->attachQueryBuilder($query);

        return $query->get();
    }
}
