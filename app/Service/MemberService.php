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

use App\Model\Member;
use App\Model\MemberFollow;
use App\Model\MemberVerification;
use App\Model\Role;
use App\Model\User;
use Carbon\Carbon;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class MemberService
{
    public const CACHE_KEY = 'member:token:';

    public const DEVICE_CACHE_KEY = 'member:device:';

    public const EXPIRE_VERIFICATION_MINUTE = 10;

    protected Redis $redis;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }

    public function apiCheckUser(array $userInfo)
    {
        $user = $this->getUserFromEmailOrAccount($userInfo['email'], $userInfo['account']);

        if (! $user) {
            return false;
        }

        if(! empty($userInfo['password'])){
            if (password_verify($userInfo['password'], $user->password)) {
                return $user;
            }
            return false;
        }
        
        return $user;
    }

    public function apiRegisterUser(array $data): Member
    {
        $name = $data['name'];
        if(empty($name)){
            $name = Member::VISITOR_NAME. substr(hash('sha256', $this->randomStr(), false), 0, 10);
        }
        $model = new Member();
        $model->name = $name;
        if(!empty($data['password'])){
            $model->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $model->sex = $data['sex'];
        $model->age = $data['age'];
        $model->avatar = $model->avatar ?? '';
        if (! empty($data['avatar'])) {
            $model->avatar = $data['avatar'];
        }
        if (! empty($data['email'])) {
            $model->email = $data['email'];
        }
        if (! empty($data['phone'])) {
            $model->phone = $data['phone'];
        }
        // $model->email = $data['email'];
        // $model->phone = $data['phone'];
        $model->status = Member::STATUS['VISITORS'];
        $model->buy_level_id = Role::API_DEFAULT_USER_ROLE_ID;
        $model->account = $data['account'];
        $model->device = $data['device'];
        $model->register_ip = $data['register_ip'];
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

        if (! empty($data['account'])) {
            $model->account = $data['account'];
            // 遊客 -> 會員未驗證
            if($model -> status == Member::STATUS['VISITORS']){
                $model -> status = Member::STATUS['NOT_VERIFIED'];
            }
        }

        if (! empty($data['device'])) {
            $model->device = $data['device'];
        }

        if (! empty($data['last_ip'])) {
            $model->last_ip = $data['last_ip'];
        }
        $model->save();
    }

    // 使用者列表
    public function getList($page, $pagePer)
    {
        // 撈取 遊客 註冊未驗證 註冊已驗證 會員
        return Member::select()->where('status', '<=', 2)->offset(($page - 1) * $pagePer)->limit($pagePer)->get();
    }

    // 使用者列表
    public function allCount(): int
    {
        return Member::count();
    }

    public function storeUser(array $data)
    {
        $model = new Member();
        if (! empty($data['id']) and Member::where('id', $data['id'])->exists()) {
            $model = Member::find($data['id']);
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
        if (! empty($data['email'])) {
            $model->email = $data['email'];
        }
        if (! empty($data['phone'])) {
            $model->phone = $data['phone'];
        }
        $model->status = $data['status'];
        $model->role_id = empty($model->role_id) ? Role::API_DEFAULT_USER_ROLE_ID : $model->role_id;
        $model->save();
    }

    public function deleteUser($id)
    {
        $record = Member::findOrFail($id);
        $record->status = User::STATUS['DELETE'];
        $record->save();
    }

    public function getVerificationCode(int $memberId): string
    {
        $now = Carbon::now()->toDateTimeString();
        $model = MemberVerification::where('member_id', $memberId)
            ->where('expired_at', '>=', $now)
            ->first();

        if (! empty($model)) {
            return $model->code;
        }

        return $this->createVerificationCode($memberId);
    }

    public function getUserFromEmailOrAccount(?string $email, ?string $account)
    {
        // if(!empty($email)){
        //     $user = Member::where('email', $email)->first();
        // }
        if(empty($user)) {
            $user = Member::where('account', $account)->first();
        }

        if (empty($user)) {
            return false;
        }

        return $user;
    }

    public function getMemberFollowList($user_id, $follow_type = '')
    {
        if (empty($follow_type)) {
            $type_arr = MemberFollow::TYPE_LIST;
        } else {
            $type_arr = [$follow_type];
        }

        foreach ($type_arr as $key => $value) {
            // image video略過 有需要再開啟
            if ($value == 'image' || $value == 'video') {
                continue;
            }
            $class_name = MemberFollow::TYPE_CORRESPOND_LIST[$value];
            switch ($value) {
                case 'image':
                    $query = $class_name::join('member_follows', function ($join) use ($class_name) {
                        $join->on('member_follows.correspond_id', '=', 'images.id')
                            ->where('member_follows.correspond_type', '=', $class_name);
                    })->select('images.id', 'images.title', 'images.thumbnail', 'images.description');
                    break;
                case 'video':
                    $query = $class_name::join('member_follows', function ($join) use ($class_name) {
                        $join->on('member_follows.correspond_id', '=', 'videos.id')
                            ->where('member_follows.correspond_type', '=', $class_name);
                    })->select('videos.*');
                    break;
                case 'actor':
                    $query = $class_name::join('member_follows', function ($join) use ($class_name) {
                        $join->on('member_follows.correspond_id', '=', 'actors.id')
                            ->where('member_follows.correspond_type', '=', $class_name);
                    })->select('actors.id', 'actors.sex', 'actors.name');
                    break;
                case 'tag':
                    $query = $class_name::join('member_follows', function ($join) use ($class_name) {
                        $join->on('member_follows.correspond_id', '=', 'tags.id')
                            ->where('member_follows.correspond_type', '=', $class_name);
                    })->select('tags.id', 'tags.name');
                    break;
                default:
                    # code...
                    break;
            }
            $result[$value] = $query->where('member_follows.member_id', '=', $user_id)->whereNull('member_follows.deleted_at')->get()->toArray();
        }

        return $result;
    }

    protected function createVerificationCode(int $memberId): string
    {
        $model = new MemberVerification();
        $model->member_id = $memberId;
        $model->code = str_random();
        $model->expired_at = Carbon::now()->addMinutes(self::EXPIRE_VERIFICATION_MINUTE)->toDateTimeString();
        $model->save();

        return $model->code;
    }

    // 亂處產生一個string
    public function randomStr($length = 8)
    {
        $url = '';
        $charray = array_merge(range('a', 'z'), range('0', '9'));
        $max = count($charray) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $randomChar = mt_rand(0, $max);
            $url .= $charray[$randomChar];
        }
        return $url;
    }
}
