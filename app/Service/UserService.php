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

    public const DEVICE_CACHE_KEY = 'user:device:';

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
        $model->sex = $data['sex'];
        $model->age = $data['age'];
        $model->avatar = $data['avatar'];
        $model->email = $data['email'];
        $model->phone = $data['phone'];
        $model->status = User::STATUS['NORMAL'];
        $model->role_id = Role::API_DEFAULT_USER_ROLE_ID;
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

    public function apiCheckUser(array $userInfo)
    {
        $user = User::where('email', $userInfo['email'])->first();
        if (! $user) {
            $user = User::where('uuid', $userInfo['uuid'])->first();
        }

        if (! $user) {
            return false;
        }

        if (password_verify($userInfo['password'], $user->password)) {
            return $user;
        }
        return false;
    }

    public function apiRegisterUser(array $data): User
    {
        $model = new User();
        $model->name = $data['name'];
        $model->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $model->sex = $data['sex'];
        $model->age = $data['age'];
        $model->avatar = $model->avatar ?? '';
        if (! empty($data['avatar'])) {
            $model->avatar = $data['avatar'];
        }
        $model->email = $data['email'];
        $model->phone = $data['phone'];
        $model->status = User::STATUS['NORMAL'];
        $model->role_id = Role::API_DEFAULT_USER_ROLE_ID;
        $model->uuid = $data['uuid'];
        $model->save();

        return $model;
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

    public function updateUser(int $id, array $data): void
    {
        $model = User::find($id);
        if (! empty($data['name']) and empty($model->name)) {
            $model->name = $data['name'];
        }

        if (! empty($data['password'])) {
            $model->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (! empty($data['sex'])) {
            $model->sex = $data['sex'];
        }

        if (! empty($data['age'])) {
            $model->age = $data['age'];
        }

        if (! empty($data['avatar'])) {
            $model->avatar = $data['avatar'];
        }

        if (! empty($data['email'])) {
            $model->email = $data['email'];
        }

        if (! empty($data['phone'])) {
            $model->phone = $data['phone'];
        }

        if (! empty($data['uuid'])) {
            $model->uuid = $data['uuid'];
        }

        $model->status = User::STATUS['NORMAL'];
        $model->role_id = Role::API_DEFAULT_USER_ROLE_ID;
        $model->save();
    }

    public function saveToken(int $userId, string $token): void
    {
        $this->redis->set(self::CACHE_KEY . $userId, $token);
    }

    public function checkAndSaveDevice(int $userId, string $uuid): bool
    {
        $key = self::DEVICE_CACHE_KEY . $userId;
        if (! $this->redis->exists($key)) {
            $today = Carbon::now()->toDateString();
            $nextDay = Carbon::parse($today . ' 00:00:00')->addDay()->timestamp;
            $expire = $nextDay - time();
            $this->redis->set($key, $uuid, $expire);
            return true;
        }

        $redisUUid = $this->redis->get($key);

        if ($redisUUid == $uuid) {
            return true;
        }

        return false;
    }
}
