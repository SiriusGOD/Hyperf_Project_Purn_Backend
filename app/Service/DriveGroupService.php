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
use App\Model\DriveGroup;
use App\Model\DriveGroupHasClass;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

use function Hyperf\Support\env;

class DriveGroupService
{
    public const CACHE_KEY = 'drive_group';

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
         $result = DriveGroup::get()->toArray();
         $this->redis->set(self::CACHE_KEY, json_encode($result));
         $this->redis->expire(self::CACHE_KEY, self::TTL_ONE_DAY);
     }

    public function storeDriveGroup(array $params): void
    {
        $model = new DriveGroup();
        if (! empty($params['id'])) {
            $model = DriveGroup::find($params['id']);
        }
        if (! empty($params['image_url'])) {
            $model->img = $params['image_url'];
            // $model->height = $data['height'];
            // $model->weight = $data['weight'];
        }
        $model->name = $params['name'];
        $model->user_id = $params['user_id'];
        $model->url = $params['url'];
        $model->save();

        if (count($params['groups']) > 0) {
            $id = $model->id;
            $this->createDriverGroupRelationship($params['groups'], $id);
        }
    }

    // 新增或更新車群關係
    public function createDriverGroupRelationship(array $groups, int $drive_group_id)
    {
        DriveGroupHasClass::where('drive_group_id', $drive_group_id)->delete();
        foreach ($groups as $key => $value) {
            $model = DriveGroupHasClass::where('drive_class_id', $value)
                ->where('drive_group_id', $drive_group_id);
            if (! $model->exists()) {
                $model = new DriveGroupHasClass();
                $model->drive_group_id = $drive_group_id;
                $model->drive_class_id = $value;
                $model->save();
            }
        }
    }

    public function deleteDriveClass(int $id): void
    {
        $model = DriveGroup::where('id', $id)->first();
        $model->delete();
        $this->updateCache();
    }

    public function getList()
    {
        $res = [];
        $drive_class = DriveClass::whereNull('deleted_at')->get();
        foreach ($drive_class as $key => $value) {
            $datas = DriveGroupHasClass::join('drive_groups', 'drive_groups.id', 'drive_group_has_class.drive_group_id')
                                        ->whereNull('drive_groups.deleted_at')
                                        ->where('drive_group_has_class.drive_class_id', $value -> id)
                                        ->select('name', 'img', 'url')
                                        ->get()
                                        ->toArray();
            if(!empty($datas)){
                // 整理
                foreach ($datas as $key2 => $data) {
                    $datas[$key]['img'] = env('IMAGE_GROUP_ENCRYPT_URL').$data['img'];
                }
                array_push($res, array(
                    'class_name' => $value -> name,
                    'description' => $value -> description,
                    'groups' => $datas
                ));
            }
        }

        return $res;
    }
}
