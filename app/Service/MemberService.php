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
use App\Model\Member;
use Carbon\Carbon;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use HyperfExt\Hashing\Hash;

class MemberService
{
    public const CACHE_KEY = 'member:token:';

    public const DEVICE_CACHE_KEY = 'user:device:';

    protected Redis $redis;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }


    public function apiCheckUser(array $userInfo)
    {
        $user = Member::where('email', $userInfo['email'])->first();
        if (! $user) {
            $user = Member::where('uuid', $userInfo['uuid'])->first();
        }

        if (! $user) {
            return false;
        }

        if (Hash::check($userInfo['password'], $user->password)) {
            return $user;
        }
        return false;
    }

    public function apiRegisterUser(array $data): Member
    {
        $model = new Member();
        $model->name = $data['name'];
        $model->password = Hash::make($data['password']);
        $model->sex = $data['sex'];
        $model->age = $data['age'];
        $model->avatar = $model->avatar ?? '';
        if (! empty($data['avatar'])) {
            $model->avatar = $data['avatar'];
        }
        $model->email = $data['email'];
        $model->phone = $data['phone'];
        $model->status = Member::STATUS['NORMAL'];
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

    public function updateUser(int $id, array $data): void
    {
        $model = Member::find($id);
        if (! empty($data['name']) and empty($model->name)) {
            $model->name = $data['name'];
        }

        if (! empty($data['password'])) {
            $model->password = Hash::make($data['password']);
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

        $model->status = Member::STATUS['NORMAL'];
        $model->role_id = Role::API_DEFAULT_USER_ROLE_ID;
        $model->save();
    }
}
