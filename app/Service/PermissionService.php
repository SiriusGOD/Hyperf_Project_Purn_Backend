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

use App\Model\Permission;
use App\Model\UserStep;
use Hyperf\DbConnection\Db;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class PermissionService
{
    protected \Psr\Log\LoggerInterface $logger;
    public $redis;
    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }

    // 取得 使用者的角色權限
    public function getUserPermission(int $role_id)
    {
        $permissionsPluck = Db::table('role_has_permissions')->where('role_id', $role_id)->pluck('permission_id', 'id');
        $permiss_id = $permissionsPluck->toArray();
        $permissionsPluck = Permission::whereIn('id', $permiss_id)->pluck('name', 'id');
        return $permissionsPluck->toArray();
    }

    // 儲存 角色權限
    public function storePermission($datas, $role_id)
    {
        Db::table('role_has_permissions')->where('role_id', $role_id)->delete();
        $insertData = [];
        foreach ($datas as $k => $p_id) {
            $insertData[] = ['role_id' => $role_id, 'permission_id' => $p_id];
        }
        Db::table('role_has_permissions')->insert($insertData);
    }

    // 取得全部權限
    public function getAll()
    {
        return Permission::all();
    }

    // 取得全部權限-存成Array
    public function parseData()
    {
        $datas = self::getAll();
        $d = [];
        foreach ($datas as $row) {
            $d[$row->main][] = ['name' => $row->name, 'id' => $row->id];
        }
        return $d;
    }

    // 取得角色權限
    public function getRolePermission($role_id)
    {
        $datas = Db::table('role_has_permissions')->where('role_id', $role_id)->get();
        $d = [];
        foreach ($datas as $row) {
            $d[] = intval($row->permission_id);
        }
        return $d;
    }

    // controller 是否有權限 middle
    public function hasPermission(array $callbacks)
    {
        $callBackStr = explode('\Admin\\', $callbacks[0]);
        $callBackStr = explode('Controller', $callBackStr[1]);
        $key = strtolower($callBackStr[0]) . '-' . $callbacks[1];
        $flag = self::checkPermission($key);
        self::recordStep($key);
        if ($flag) {
            return true;
        }
        return false;
    }

    // 記錄使用者的操作日誌
    public function recordStep(string $key)
    {
        $model = new UserStep();
        $model->user_id = auth('session')->user()->id; 
        $model->user_name = auth('session')->user()->name; 
        $model->role_id = auth('session')->user()->role_id; 
        $model->action = $key; 
        $model->comment = cutStrLang($key) ; 
        $model->save(); 
    }
    // 使用者的權限
    public function checkPermission(string $key)
    {
        $user_id = auth('session')->user()->id;
        $role_id = auth('session')->user()->role_id;
        if (empty($user_id) || empty($role_id)) {
            return false;
        }
        // 如果是超極管理員
        if ($role_id == 1) {
            return true;
        }
        $redisKey = \App\Constants\Constants::USER_PERMISSION_KEY . $user_id;
        if ($this->redis->exists($redisKey)) {
            $perArys = json_decode($this->redis->get($redisKey), true);
        } else {
            $perArys = self::getUserPermission($role_id);
            // save permission to redis
            $this->redis->set($redisKey, json_encode($perArys), 60 * 60 * 3);
        }
        return in_array($key, $perArys) ? true : false;
    }

    // 權限重設
    public function resetPermission()
    {
        $user_id = auth('session')->user()->id;
        $role_id = auth('session')->user()->role_id;
        $redisKey = \App\Constants\Constants::USER_PERMISSION_KEY . $user_id;
        $perArys = self::getUserPermission($role_id);
        // save permission to redis
        redis()->set($redisKey, json_encode($perArys), 3 * 60 * 60);
    }
}
