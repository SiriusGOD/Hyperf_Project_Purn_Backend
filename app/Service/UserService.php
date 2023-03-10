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

use App\Model\User;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use HyperfExt\Hashing\Hash;

class UserService
{
    public const CACHE_KEY = 'user:';

    protected Redis $redis;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }

    // 取得 user
    public function getUserById(int $id)
    {
        if ($this->redis->exists(self::CACHE_KEY)) {
            return $this->redis->get(self::CACHE_KEY);
        }
        $user = User::find($id);
        $this->redis->set(self::CACHE_KEY . $id, $user);
        return $user->id;
    }

    // 搜尋用戶
    public function findUser(int $id)
    {
        return User::findOrFail($id);
    }

    // 更新或新增 User
    public function storeUser(array $data)
    {
        if ($data['id']) {
            $record = $this->findUser(intval($data['id']));
        } else {
            $record = new User();
            $record->name = $data['name'];
            $record->email = $data['name'] . '@gmail.com';
            $record->phone = rand(123111, 123456789);
        }
        if (! empty($data['password'])) {
            $record->password = Hash::make($data['password']);
        }
        $record->role_id = isset($data['role_id']) ? $data['role_id'] : $record->role_id;
        $record->avatar = $data['avatar'];
        $record->save();
    }

    // 刪除 User
    public function deleteUser($id)
    {
        $record = $this->findUser(intval($id));
        $record->status = User::STATUS_DELETE;
        $record->save();
    }

    // 使用者列表
    public function getList($page, $pagePer)
    {
        return User::select()->where('status',1)->offset(($page - 1) * $pagePer)->limit($pagePer)->get();
    }

    // 使用者列表
    public function allCount()
    {
        return User::count();
    }
    // 使用者列表
    public function userRoleUpdate($roldId)
    {
        User::where('role_id',$roldId)->update(['role_id'=>'']);
    }


    //登入驗證
    public function checkUser(array $userInfo)
    {
        $user = User::where('name',$userInfo['name'])->first();
        if(!$user){
            return false;
        }else{
            if(Hash::check($userInfo["password"], $user->password)){
                return $user;
            }else{
                return false;
            }
        }
    }


}
