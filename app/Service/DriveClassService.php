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

use App\Model\DriveClass;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

use function Hyperf\Support\env;

class DriveClassService
{
    public const CACHE_KEY = 'drive_class';

    public const TTL_ONE_DAY = 86400;
    public const TTL_30_MIN = 1800;

    public Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

     // 更新快取
     public function updateCache(): void
     {
         $result = DriveClass::get()->toArray();
         $this->redis->set(self::CACHE_KEY, json_encode($result));
         $this->redis->expire(self::CACHE_KEY, self::TTL_ONE_DAY);
     }

    public function storeDriveClass(array $data): void
    {
        $model = DriveClass::findOrNew($data['id']);
        $model->user_id = $data['user_id'];
        $model->name = $data['name'];
        $model->description = $data['description'];
        $model->save();
        $this->updateCache();
    }

    public function deleteDriveClass(int $id): void
    {
        $model = DriveClass::where('id', $id)->first();
        $model->delete();
        $this->updateCache();
    }
}
