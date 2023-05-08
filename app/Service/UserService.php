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
use App\Model\User;
use Carbon\Carbon;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class UserService
{
    public const CACHE_KEY = 'user:token:';

    protected Redis $redis;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }

    // 搜尋用戶
    public function findUser(int $id)
    {
        return User::findOrFail($id);
    }

    // 更新或新增 User
    public function storeUser(array $data)
    {
        $model = new User();

        if (! empty($data['id']) and User::where('id', $data['id'])->exists()) {
            $model = User::find($data['id']);
        }

        if (! empty($data['name']) and empty($model->name)) {
            $model->name = $data['name'];
        }
        if (! empty($data['password'])) {
            $model->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $model->sex = isset($data['sex'])?$data['sex']:1;
        $model->age = isset($data['age'])?$data['age']:18;
        $model->email = isset($data['email'])?$data['email']:"a".time()."@gmail.com";
        if (! empty($data['phone'])) {
          $model->phone = $data['phone'];
        }
        if (! empty($data['avatar'])) {
          $model->avatar = $data['avatar'];
        }
        if (! empty($data['status'])) {
          $model->status = $data['status'];
        }
        if (! empty($data['role_id'])) {
          $model->role_id = $data['role_id'];
        }
        $model->save();
    }

    // 刪除 User
    public function deleteUser($id)
    {
        $record = $this->findUser(intval($id));
        $record->status = User::STATUS['DELETE'];
        $record->save();
    }

    // 使用者列表
    public function getList($page, $pagePer)
    {
        return User::select()->where('status', 1)->offset(($page - 1) * $pagePer)->limit($pagePer)->get();
    }

    // 使用者列表
    public function allCount()
    {
        return User::count();
    }

    // 使用者列表
    public function userRoleUpdate($roldId)
    {
        User::where('role_id', $roldId)->update(['role_id' => '']);
    }

    // 登入驗證
    public function checkUser(array $userInfo)
    {
        $user = User::where('name', $userInfo['name'])->first();
        if (! $user) {
            return false;
        }
        if (password_verify($userInfo['password'], $user->password)) {
            return $user;
        }
        return false;
    }

    public function moveUserAvatar($file): string
    {
        $extension = $file->getExtension();
        $filename = sha1(Carbon::now()->toDateTimeString());
        if (! file_exists(BASE_PATH . '/public/avatar')) {
            mkdir(BASE_PATH . '/public/avatar', 0755);
        }
        $imageUrl = '/image/' . $filename . '.' . $extension;
        $path = BASE_PATH . '/public' . $imageUrl;
        $file->moveTo($path);

        return $imageUrl;
    }
}
