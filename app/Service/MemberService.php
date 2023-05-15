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

use App\Constants\MemberCode;
use App\Model\BuyMemberLevel;
use App\Model\ImageGroup;
use App\Model\Member;
use App\Model\MemberFollow;
use App\Model\MemberLevel;
use App\Model\MemberVerification;
use App\Model\Order;
use App\Model\Product;
use App\Model\Role;
use App\Model\User;
use App\Model\Video;
use App\Model\ActorCorrespond;
use App\Model\Coin;
use Carbon\Carbon;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Str;

class MemberService extends BaseService
{
    public const CACHE_KEY = 'member:token:';

    public const KEY = 'member:';

    public const DEVICE_CACHE_KEY = 'member:device:';

    public const EXPIRE_VERIFICATION_MINUTE = 10;
    public const LOGIN_LIMIT_CACHE_KEY = 'login_limit:';
    public const DAY = 86400;
    protected Redis $redis;
    protected $memberInviteLogService;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        Redis $redis,
        MemberInviteLogService $memberInviteLogService,
        LoggerFactory $loggerFactory
    ) {
        $this->redis = $redis;
        $this->memberInviteLogService = $memberInviteLogService;
        $this->logger = $loggerFactory->get('reply');
    }

    public function apiGetUser(array $userInfo)
    {
        $user = $this->getUserFromAccountOrEmail($userInfo['account']);

        if (! $user) {
            return false;
        }

        return $user;
    }
    //登入次數限制上限三次
    public function loginLimit($deviceId)
    {
        if (empty($deviceId)) {
          return [
                'code' => MemberCode::EMPTY_DEVICE_ERROR,
                'msg' => trans('validation.required', ['attribute' => 'device_id']),
            ];
        }
        $key = self::LOGIN_LIMIT_CACHE_KEY . $deviceId;
        if ($this->redis->exists($key) and $this->redis->get($key) >= ((int)env('LOGIN_LIMIT')) ) {
          return [
                'code' => MemberCode::TRY_LIMIT_ERROR,
                'msg' => trans('validation.try_limit'),
            ];
        }
        return false;
    }

    public function checkPassword($plain, $hash): bool
    {
        if (password_verify($plain, $hash)) {
            return true;
        }
        return false;
    }

    public function apiRegisterUser(array $data): Member
    {
        $name = $data['name'];
        if (empty($name)) {
            $name = Member::VISITOR_NAME . substr(hash('sha256', $this->randomStr(), false), 0, 10);
        }
        $model = new Member();
        $model->name = $name;
        if (! empty($data['password'])) {
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
        $model->member_level_status = Role::API_DEFAULT_USER_ROLE_ID;
        $model->account = $data['account'];
        $model->device = $data['device'];
        $model->register_ip = $data['register_ip'];
        $model->aff = Str::random(5);
        $model->aff_url = $data['aff_url'];;
        try {
          // code that may throw an exception
          if($model->save()){
            self::afterRegister($model, $data);
            return $model;
          }else{
            $str= "something errors ";
            errLog($str);
            return false;
          }
          
        } catch (\Exception $e) {
          // handle the exception
          $str= "An error occurred: " . $e->getMessage();
          errLog($str);
          return false;
        }
  }

    // 代理Log
    public function afterRegister(Member $model, array $data)
    {
        if (! empty($data['invited_code'])) {
            $member = self::getMmemberByAff($data['invited_code']);
            if ($member) {
                $model->invited_by = $member->id;
                $insert['invited_by'] = $member->id;
                $insert['member_id'] = $model->id;
                $insert['level'] = 1;
                $insert['invited_code'] = $data['invited_code'];
                $this->memberInviteLogService->initRow($insert);
                $this->memberInviteLogService->calcProxy($model);
            }
        }
        $model->save();
    }

    // 推廣碼找代理
    public function getMmemberByAff(string $aff): Member
    {
        return Member::where('aff', $aff)->first();
    }

    // 找一個代理
    public function getProxy(): Member
    {
        return Member::where('aff', '!=', '')->orderBy('id', 'desc')->first();
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

    // token 存入redis
    public function saveToken(int $userId, string $token): void
    {
        $key = self::CACHE_KEY . $userId;
        $this->redis->set($key, $token);
        $this->redis->expire($key, self::DAY);
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
            if ($model->status == Member::STATUS['VISITORS']) {
                $model->status = Member::STATUS['NOT_VERIFIED'];
            }
        }

        if (! empty($data['device'])) {
            $model->device = $data['device'];
        }

        if (! empty($data['last_ip'])) {
            $model->last_ip = $data['last_ip'];
        }
        $model->save();

        $this -> delRedis($id);
    }

    // 使用者列表
    public function getList($page, $pagePer)
    {
        // 撈取 遊客 註冊未驗證 註冊已驗證 會員
        return Member::select('*')->where('status', '<=', 2)->offset(($page - 1) * $pagePer)->limit($pagePer)->orderBy('id', 'desc')->get();
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
        $model->member_level_status = $data['member_level_status'];
        $model->coins = $data['coins'];
        $model->diamond_coins = $data['diamond_coins'];
        $model->diamond_quota = $data['diamond_quota'];
        $model->vip_quota = $data['vip_quota'];
        $model->free_quota = $data['free_quota'];
        $model->free_quota_limit = $data['free_quota_limit'];

        $model->save();

        if ($data['member_level_status'] > MemberLevel::NO_MEMBER_LEVEL) {
            if (! empty($data['id']) and BuyMemberLevel::where('member_id', $data['id'])->where('member_level_type', MemberLevel::TYPE_LIST[$data['member_level_status'] - 1])->whereNull('deleted_at')->exists()) {
                $buy_model = BuyMemberLevel::where('member_id', $data['id'])->where('member_level_type', MemberLevel::TYPE_LIST[$data['member_level_status'] - 1])->whereNull('deleted_at')->first();
                $buy_model->start_time = $data['start_time'];
                $buy_model->end_time = $data['end_time'];
                $buy_model->save();
            } else {
                $buy_model = new BuyMemberLevel();
                $buy_model->member_id = $model->id;
                $buy_model->member_level_type = MemberLevel::TYPE_LIST[$data['member_level_status'] - 1];
                $buy_model->member_level_id = 0;
                $buy_model->order_number = '';
                $buy_model->start_time = $data['start_time'];
                $buy_model->end_time = $data['end_time'];
                $buy_model->save();
            }
        }
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
    //刪除member Redis
    public function delRedis(int $memberId){
        $key = $this->defaultKey($memberId);
        if($this->redis->exists($key)){
        $this->redis->delete($key);
        }
    }

    //預設的key
    public function defaultKey(int $id):string
    {
        return self::KEY . ':' . $id;
    }
    // 用id找用戶
    public function getMember($id)
    {
        $key = $this->defaultKey($id);
        if ($this->redis->exists($key)) {
            $res = $this->redis->get($key);
            return json_decode($res, true);
        }
        $user = Member::where("id",$id)->first();
        $user = $user->toArray();
        $urls = 
        [
        env("PURL1"),
        env("PURL2"),
        env("DURL1"),
        env("DURL2"),
        ];
        $url = $urls[rand(0,count($urls)-1)];
        $user["aff_url"] = $url."?invited_code=".$user['aff'];

        // 撈取會員天數
        $member_level_status = $user["member_level_status"];
        if($member_level_status == MemberLevel::NO_MEMBER_LEVEL){
            $user["member_level_duration"] = 0;
        }else if($member_level_status == MemberLevel::TYPE_VALUE['vip']){
            // vip
            $date_diff = BuyMemberLevel::where('member_id', $id)->where('member_level_type', MemberLevel::TYPE_LIST[0])->whereNull('deleted_at')->selectRaw('DATEDIFF(end_time, start_time) as date, DATEDIFF(end_time, now()) as now')->first();
            if($date_diff->date >= 3650){
                $user["member_level_duration"] = null;
            }else{
                $user["member_level_duration"] = (int)$date_diff->now;
            }
        }else{
            // 鑽石
            $date_diff = BuyMemberLevel::where('member_id', $id)->where('member_level_type', MemberLevel::TYPE_LIST[1])->whereNull('deleted_at')->selectRaw('DATEDIFF(end_time, start_time) as date, DATEDIFF(end_time, now()) as now')->first();
            if($date_diff->date >= 3650){
                $user["member_level_duration"] = null;
            }else{
                $user["member_level_duration"] = (int)$date_diff->now;
            }
        }

        $this->redis->set($key, json_encode($user));
        $this->redis->expire($key, 86400);
        return $user;
    }

    public function getUserFromAccountOrEmail(?string $account, ?string $email = null)
    {
        $user = [];

        if (! empty($account)) {
            $user = Member::where('account', $account)->first();
        }

        if (empty($user) and ! empty($email)) {
            $user = Member::where('email', $email)->first();
        }

        if (empty($user)) {
            return false;
        }

        return $user;
    }
    //使用者追蹤清單
    public function getMemberFollowList($user_id, $follow_type = '',int $page ,int $limit)
    {
        if (empty($follow_type)) {
            $type_arr = MemberFollow::TYPE_LIST;
        } else {
            $type_arr = [$follow_type];
        }
        if($page == 1 ){
          $page=0;
        }
        foreach ($type_arr as $key => $value) {
            // image video tag略過 有需要再開啟
            if ($value == 'image' || $value == 'video' || $value == 'tag') {
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
                    })->select('actors.id', 'actors.name', 'actors.avatar');
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

            $query = $query->where('member_follows.member_id', '=', $user_id)
                      ->whereNull('member_follows.deleted_at')
                      ->offset($limit*$page)
                      ->limit($limit)
                      ->get()
                      ->toArray();
            
            if($value == 'actor'){
              foreach ($query as $key2 => $value2) {
                  // avatar加上網域
                  if(!empty($value2['avatar']))$query[$key2]['avatar'] = env('TEST_IMG_URL').$value2['avatar'];
                  // 查詢作品數
                  $numberOfWorks = ActorCorrespond::where('actor_id', $value2['id'])->count();
                  $query[$key2]['numberOfWorks'] = $numberOfWorks;
              }
            }
            $result[$value] = $query;
        }

        return $result;
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

    public function createOrUpdateLoginLimitRedisKey(string $deviceId)
    {
        $now = Carbon::now()->timestamp;
        $tomorrow = Carbon::tomorrow()->setHour(0)->setMinute(0)->setSecond(0)->timestamp;
        $expire = $tomorrow - $now;

        if ($this->redis->exists(self::LOGIN_LIMIT_CACHE_KEY . $deviceId)) {
            $this->redis->incr(self::LOGIN_LIMIT_CACHE_KEY . $deviceId);
        } else {
            $this->redis->set(self::LOGIN_LIMIT_CACHE_KEY . $deviceId, 1, $expire);
        }
    }

    public function getMemberProductId($memberId, $type, $page, $pageSize = 0): array
    {
        // 顯示幾筆
        $step = $pageSize ?? Order::FRONTED_PAGE_PER;

        $query = Order::join('order_details', 'order_details.order_id', 'orders.id')
            ->join('products', 'products.id', 'order_details.product_id')
            ->select('products.id', 'products.type', 'products.correspond_id', 'products.name', 'products.expire', 'orders.currency', 'orders.created_at')
            ->where('orders.user_id', $memberId)
            ->where('orders.status', Order::ORDER_STATUS['finish']);
        switch ($type) {
            case 'all':
                $query = $query->whereIn('products.type', [ImageGroup::class, Video::class]);
                break;
            case 'image':
                $query = $query->where('products.type', ImageGroup::class);
                break;
            case 'video':
                $query = $query->where('products.type', Video::class);
                break;
            default:
                $query = $query->whereIn('products.type', [ImageGroup::class, Video::class]);
                break;
        }

        if (! empty($page) && $page > 0) {
            // $query = $query -> offset($offset);
            $query = $query->offset(($page - 1) * $step);
        }
        if (! empty($limit)) {
            // $query = $query -> limit($limit);
            $query = $query->limit($step);
        }
        $model = $query->get();
        if (! empty($model)) {
            $image_arr = [];
            $video_arr = [];
            // ActorClassification::findOrFail($id);
            foreach ($model as $key => $value) {
                // 用免費次數購買的免費商品 過隔天五點就不顯示在已購買項目中
                if($value -> currency == Order::PAY_CURRENCY['free_quota']){
                    if($value -> created_at < Carbon::now()->toDateString())continue;
                }

                if ($value->type == Product::TYPE_CORRESPOND_LIST['image']) {
                    // $image = ImageGroup::findOrFail($value->correspond_id);
                    $image = ImageGroup::leftJoin('images', 'image_groups.id', 'images.group_id')
                        ->selectRaw('image_groups.thumbnail, count(*) as count')
                        ->groupBy('image_groups.id')
                        ->first();

                    array_push($image_arr, [
                        'product_id' => $value->id,
                        'source_id' => $value->correspond_id,
                        'name' => $value->name,
                        'thumbnail' => env('IMG_DOMAIN') . $image->thumbnail ?? '',
                        'expire' => $value->expire,
                        'num' => $image->count ?? 0,
                    ]);
                }
                
                if ($value->type == Product::TYPE_CORRESPOND_LIST['video']) {
                    
                    $video = Video::findOrFail($value->correspond_id);
                    array_push($video_arr, [
                        'product_id' => $value->id,
                        'source_id' => $value->correspond_id,
                        'name' => $value->name,
                        'thumbnail' => env('IMG_DOMAIN') . $video->cover_thumb ?? '',
                        'expire' => $value->expire,
                        'duration' => $value->duration ?? 0,
                    ]);
                }
            }
            $data['image'] = $image_arr;
            $data['video'] = $video_arr;
        } else {
            $data['image'] = [];
            $data['video'] = [];
        }
        return $data;
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

    // 獲取會員訂單清單
    public function getMemberOrderList($user_id, $page, $limit)
    {
        // redis
        $checkRedisKey = self::KEY.":MemberOrderList:".$user_id.":".Carbon::now()->toDateString().":".$page.":".$limit;
        if ($this->redis->exists($checkRedisKey)) {
            $jsonResult = $this->redis->get($checkRedisKey);
            return json_decode($jsonResult, true);
        }

        // 顯示幾筆
        $step = $limit ?? Order::FRONTED_PAGE_PER;

        // 撈取資料
        $products_type = [Product::TYPE_CORRESPOND_LIST['member'], Product::TYPE_CORRESPOND_LIST['points']];
        $query = Order::join('order_details', 'orders.id', 'order_details.order_id')
                        ->join('products', 'order_details.product_id', 'products.id')
                        ->join('pays', 'orders.payment_type', 'pays.id')
                        ->selectRaw('products.name as product_name, orders.created_at, orders.status, orders.currency, orders.total_price, pays.name as pay_name, products.type, products.correspond_id')
                        ->where('orders.user_id', $user_id)
                        ->whereIn('products.type', $products_type)
                        ->orderBy('orders.created_at', 'desc');
        if (! empty($page) && $page > 0) {
            $query = $query->offset(($page - 1) * $step);
        }
        if (! empty($limit)) {
            $query = $query->limit($step);
        }
        $orders = $query->get();

        // 整理資料
        $data = [];
        foreach ($orders as $key => $order) {
            switch ($order -> type) {
                case Product::TYPE_CORRESPOND_LIST['member']:
                    $member_level_type = MemberLevel::where('id', $order -> correspond_id)->select('type')->first();
                    if($member_level_type -> type == MemberLevel::TYPE_LIST[0]){
                        $data[$key]['product_name'] = trans('api.member_control.vip_level') . '-' . $order -> product_name;
                    }else{
                        $data[$key]['product_name'] = trans('api.member_control.diamond_level') . '-' . $order -> product_name;
                    }
                    break;
                case Product::TYPE_CORRESPOND_LIST['points']:
                    $coin_type = Coin::where('id', $order -> correspond_id)->select('type')->first();
                    if($coin_type -> type == Coin::TYPE_LIST[0]){
                        $data[$key]['product_name'] = trans('api.member_control.cash_coin') . '-' . $order -> product_name;
                    }else{
                        $data[$key]['product_name'] = trans('api.member_control.diamond_coin') . '-' . $order -> product_name;
                    }
                    break;
            }
            $data[$key]['created_at'] = $order -> created_at -> format('Y.m.d'); ;
            $data[$key]['status'] = trans('default.order_control.order_status_fronted_msg')[$order -> status];
            switch ($order -> status) {
                case Order::ORDER_STATUS['create']:
                    $data[$key]['statusColor'] = Order::STATUS_COLOR['yellow'];
                    break;
                case Order::ORDER_STATUS['delete']:
                    $data[$key]['statusColor'] = Order::STATUS_COLOR['red'];
                    break;
                case Order::ORDER_STATUS['finish']:
                    $data[$key]['statusColor'] = Order::STATUS_COLOR['black'];
                    break;
                case Order::ORDER_STATUS['failure']:
                    $data[$key]['statusColor'] = Order::STATUS_COLOR['orange'];
                    break;
                default:
                    $data[$key]['statusColor'] = Order::STATUS_COLOR['yellow'];
                    break;
            }
            $data[$key]['price'] = $order -> total_price;
            // if($order -> currency == Product::CURRENCY[0]){
            //     $data[$key]['price'] .= " " . trans('api.member_control.dollar');
            // }else if($order -> currency == Product::CURRENCY[1]){
            //     $data[$key]['price'] .= " " . trans('api.member_control.point');
            // }
            $data[$key]['pay_method'] =  $order -> pay_name;
        }

        $this->redis->set($checkRedisKey, json_encode($data));
        $this->redis->expire($checkRedisKey, self::DAY);

        return $data;
    }

      // 獲取個人推薦商品 (上架中的) (暫時遮掉)
      // public function getPersonalList($user_id, $method, $offset, $limit)
      // {
    //     //
    //     $half_offset = ceil($offset/2);

    //     // 扣掉廣告
    //     $limit = $limit - 1 ;
    //     $image_limit = floor($limit/2);
    //     $video_limit = ceil($limit/2);

    //     //
    //     if($method == 'most'){
    //         var_dump($method);
    //         $sub_query = OrderDetail::selectRaw('count(*) as count')->whereColumn('order_details.product_id', '=', 'products.id');
    //     }

    //     // 獲取該會員前五個點擊標籤
    //     $tags = [];
    //     $member_tags = MemberTag::select('tag_id')->where('member_id', $user_id)->OrderBy('count')->limit(5)->get();
    //     foreach ($member_tags as $key => $member_tag) {
    //         array_push($tags, $member_tag -> tag_id);
    //     }

    //     // 撈取包含這五個標籤的上架商品 (圖片)
    //     $type = Product::TYPE_LIST[0];
    //     $img_query = ImageGroup::join('tag_corresponds', function ($join) use ($type, $tags) {
    //                             $join->on('image_groups.id', '=', 'tag_corresponds.correspond_id')
    //                                 ->where('tag_corresponds.correspond_type', Product::TYPE_CORRESPOND_LIST[$type])
    //                                 ->whereIn('tag_corresponds.tag_id',$tags)
    //                                 ->join('products', function ($join) use ($type) {
    //                                     $join->on('products.correspond_id', '=', 'tag_corresponds.correspond_id')
    //                                     ->where('products.type',$type)
    //                                     ->where('products.expire',Product::EXPIRE['no']);
    //                                 });
    //                         })->selectRaw('products.id, products.name, products.type as product_type, products.correspond_id, products.currency, products.selling_price, products.diamond_price, image_groups.pay_type, image_groups.thumbnail, (select count(*) from images where group_id = image_groups.id ) as num');

    //     // 撈取包含這五個標籤的上架商品 (影片)
    //     $type = Product::TYPE_LIST[1];
    //     $video_query = Video::join('tag_corresponds', function ($join) use ($type, $tags) {
    //                             $join->on('videos.id', '=', 'tag_corresponds.correspond_id')
    //                                 ->where('tag_corresponds.correspond_type', $type)
    //                                 ->whereIn('tag_corresponds.tag_id',$tags)
    //                                 ->join('products', function ($join) use ($type) {
    //                                     $join->on('products.correspond_id', '=', 'tag_corresponds.correspond_id')
    //                                     ->where('products.type',$type)
    //                                     ->where('products.expire',Product::EXPIRE['no']);
    //                                 });
    //                         })->selectRaw('products.id, products.name, products.type as product_type, products.correspond_id, products.currency, products.selling_price, products.diamond_price, videos.is_free as pay_type, videos.cover_thumb as thumbnail, videos.duration');

    //     if(!empty($offset)){
    //         $img_query = $img_query -> offset($half_offset);
    //         $video_query = $video_query -> offset($half_offset);
    //     }
    //     if(!empty($limit)){
    //         $img_query = $img_query -> limit($image_limit);
    //         if($img_query -> count() < $image_limit){
    //             $video_query = $video_query -> limit($video_limit + ($image_limit - $img_query -> count()));
    //         }else{
    //             $video_query = $video_query -> limit($video_limit);
    //         }
    //     }
    //     switch ($method) {
    //         case 'recommend':
    //             // 個人推薦
    //             $images = $img_query -> groupBy('products.id') -> get();
    //             $videos = $video_query -> groupBy('products.id') -> get();
    //             break;
    //         case 'new':
    //             // 個人推薦
    //             $images = $img_query -> groupBy('products.id') -> orderBy('products.updated_at', 'desc') -> get();
    //             $videos = $video_query -> groupBy('products.id') -> orderBy('products.updated_at', 'desc') -> get();
    //             break;
    //         case 'most':
    //             // 個人推薦
    //             $images = $img_query -> selectRaw('(select count(*) from order_details where order_details.product_id = products.id) as count') -> groupBy('products.id') -> orderBy('count', 'desc') -> get();
    //             $videos = $video_query -> selectRaw('(select count(*) from order_details where order_details.product_id = products.id) as count') -> groupBy('products.id') -> orderBy('count', 'desc') -> get();
    //             break;
    //         default:
    //             # code...
    //             break;
    //     }
    //     $merge_arr = $this->shuffleMergeArray($images->toArray(), $videos->toArray());

    //     // 撈出圖片廣告
    //     $ads = Advertisement::select('name', 'image_url', 'url')
    //                         ->where('position', Advertisement::POSITION['ad_image'])
    //                         ->where('expire', Product::EXPIRE['no'])
    //                         ->inRandomOrder()->take(1)->get()->toArray();
    //     $result = array_merge($ads, $merge_arr);
    //     foreach ($result as $key => $value) {
    //         if(! empty($result[$key]['thumbnail']))$result[$key]['thumbnail'] = env('IMG_DOMAIN') . $value['thumbnail'];
    //         if(! empty($result[$key]['image_url']))$result[$key]['image_url'] = env('IMG_DOMAIN') . $value['image_url'];
    //     }
    //     return $result;
      // }

    // 隨機合併兩個陣列元素，保持原有資料的排序不變（即各個陣列的元素在合併後的陣列中排序與自身原來一致）
    protected function shuffleMergeArray($array1, $array2)
    {
        $mergeArray = [];
        $sum = count($array1) + count($array2);
        for ($k = $sum; $k > 0; --$k) {
            $number = mt_rand(1, 2);
            if ($number == 1) {
                $mergeArray[] = $array2 ? array_shift($array2) : array_shift($array1);
            } else {
                $mergeArray[] = $array1 ? array_shift($array1) : array_shift($array2);
            }
        }
        return $mergeArray;
    }

    // 刪除前台演員分類的快取
    public function delFrontCache()
    {
        $service = make(ActorClassificationService::class);
        $service->delRedis();
    }

    // 刪除 會員購買紀錄 Redis
    public function delMemberListRedis($user_id){
        $checkRedisKey = self::KEY.":MemberOrderList:".$user_id.":".Carbon::now()->toDateString();
        $keys = $this->redis->keys( $checkRedisKey.'*');
        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    // 更新會員的演員追蹤快取
    public function updateMemberFollowCache($user_id)
    {
        $service = make(ActorService::class);
        $service->updateIsExistCache($user_id);
    }

    // 刪除會員快取
    public function delMemberRedis($user_id)
    {
        $key = $this->defaultKey($user_id);
        $this->redis->del($key);
    }
}
