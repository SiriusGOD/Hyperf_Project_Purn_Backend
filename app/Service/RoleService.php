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

use App\Model\Role;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;


class RoleService
{
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }

    // 取得全部角色
    public function getAll()
    {
        return Role::all();
    }

    // 取得全部角色
    public function allCount()
    {
        return Role::count();
    }
    //取得角色
    public function findRole($id)
    {
        return Role::findOrFail($id);
    }

    //新增角色 回傳角色
    public function storeRole($data)
    {
        if ($data['id']) {
            $record = self::findRole(intval($data['id']));
        } else {
            $record = new Role();
        }
        $record->name = $data['name'];
        $record->save();
        return $record;
    }

    //刪除角色
    public function delRole($id)
    {
        $role=Role::findOrFail($id);
        if($role){
            $role->delete();
        }
    }

}
