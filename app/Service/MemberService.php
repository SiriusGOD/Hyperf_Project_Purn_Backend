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
    //推薦優惠
    public const AFF = 'aff';
    //兌換
    public const REDEEM = 'redeem';

    public const DEVICE_CACHE_KEY = 'member:device:';

    public const EXPIRE_VERIFICATION_MINUTE = 10;
    public const LOGIN_LIMIT_CACHE_KEY = 'login_limit:';
    public const DAY = 86400;
    public const HOUR = 3720;
    protected Redis $redis;
    protected $memberInviteLogService;
    protected $channelService;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        Redis $redis,
        MemberInviteLogService $memberInviteLogService,
        ChannelService $channelService,
        LoggerFactory $loggerFactory
    ) {
        $this->redis = $redis;
        $this->memberInviteLogService = $memberInviteLogService;
        $this->channelService = $channelService;
        $this->logger = $loggerFactory->get('reply');
    }
    //推薦會員 推薦的人會有二天VIP 
    public function affUpgradeVIP(int $member_id ,int $days = 1 ,string $cate=self::REDEEM)
    {
      $member = self::getMemberSimple($member_id ,"*");
      //VIP 1 天50次 ， 二天以上NULL
      $mlevel = MemberLevel::where('type','vip')->where('duration',1)->first(); 
      $obj = BuyMemberLevel::where('member_id', $member_id)->where('order_number',self::REDEEM)->first();
      if($obj){
        $member->vip_quota = Member::VIP_QUOTA['UP_TWO'];
        $obj->end_time = Carbon::parse($obj->end_time)->addDay($days)->toDateTimeString();
        $obj->save();
      }else{
        $insert['member_id'] = $member_id; 
        $insert['member_level_type'] = $mlevel->type;  
        $insert['member_level_id'] = $mlevel->id; 
        $insert['order_number'] = $cate; 
        $insert['start_time'] = Carbon::now()->toDateTimeString();
        $insert['end_time'] = Carbon::now()->addDay($days)->toDateTimeString();
        $this->modelStore(BuyMemberLevel::class , $insert);
        //更新使用者
        if($days>=2){
          $member->vip_quota = Member::VIP_QUOTA['UP_TWO'];
        }elseif($days==1){
          $member->vip_quota = Member::VIP_QUOTA['DAY'];
        }
      }
      $member->member_level_status = MemberLevel::TYPE_VALUE['vip'];
      $member->save();

      errLog('新增會員VIP 一天');
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
        if (! empty($data['free_quota'])) {
            $model->free_quota = $data['free_quota'];
        }

        // $model->email = $data['email'];
        // $model->phone = $data['phone'];
        $model->status = Member::STATUS['VISITORS'];
        $model->member_level_status = Role::API_DEFAULT_USER_ROLE_ID;
        $model->account = $data['account'];
        $model->device = $data['device'];
        $model->register_ip = $data['register_ip'];
        $model->aff = Str::random(5);
        $model->aff_url = $data['aff_url'];
        try {
          // code that may throw an exception
          if($model->save()){
            if(!empty($data['aff_url'])){
              //渠道用計數註冊人數 - 先存作redis  
              //再用TASK寫入DB
              $this->channelService->setChannelRedis($data['aff_url'] , "member");
            }
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
                $model->free_quota = $model->free_quota + MemberLevel::ADD_QUOTA;
                $model->free_quota_limit = $model->free_quota_limit + MemberLevel::ADD_QUOTA;
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
        if (! empty($data['account']) and empty($model->account)) {
            $model->account = $data['account'];
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

        $this->delRedis($model->id);

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
        }else{
            // 移除會員等級的持續時間
            $buy_models = BuyMemberLevel::select('id')->where('member_id', $data['id'])->whereNull('deleted_at')->get();
            foreach ($buy_models as $key => $buy_model) {
                $model = BuyMemberLevel::where('id', $buy_model->id)->first();
                $model -> delete();
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
    public function getMemberSimple($id , $select)
    {
        return Member::select($select)->where("id",$id)->first();
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
                    if(!empty($value2['avatar'])){
                        $query[$key2]['avatar'] = env('TEST_IMG_URL').$value2['avatar'];
                    }else{
                        $query[$key2]['avatar'] = '';
                    }
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

    public function getMemberProductId($memberId, $type, $page, $pageSize = 0, $is_asc): array
    {
        // 顯示幾筆
        $step = $pageSize ?? Order::FRONTED_PAGE_PER;

        $generateService = make(GenerateService::class);

        $query = Order::join('order_details', 'order_details.order_id', 'orders.id')
            ->join('products', 'products.id', 'order_details.product_id')
            ->select('products.id', 'products.type', 'products.correspond_id', 'products.name', 'products.expire', 'orders.currency', 'orders.created_at')
            ->where('orders.user_id', $memberId)
            ->where('orders.status', Order::ORDER_STATUS['finish']);
        switch ($type) {
            case 'all':
                $query = $query->whereIn('products.type', [ImageGroup::class, Video::class]);
                break;
            case 'image_group':
                $query = $query->where('products.type', ImageGroup::class);
                break;
            case 'video':
                $query = $query->where('products.type', Video::class);
                break;
            default:
                $query = $query->whereIn('products.type', [ImageGroup::class, Video::class]);
                break;
        }

        if ($is_asc == 1) {
            $query = $query->orderBy('orders.created_at');
        } else {
            $query = $query->orderByDesc('orders.created_at');
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
            $arr = [];
            foreach ($model as $key => $value) {
                // 用免費次數購買的免費商品 過隔天五點就不顯示在已購買項目中
                if($value -> currency == Order::PAY_CURRENCY['free_quota']){
                    if($value -> created_at < Carbon::now()->toDateString())continue;
                }

                if ($value->type == Product::TYPE_CORRESPOND_LIST['image']) {
                    $image = ImageGroup::with('imagesLimit')->where('id', $value->correspond_id)->get()->toArray();
                    $result = $generateService->generateImageGroups([], $image);     
                    array_push($arr, $result[0]);
                }
                
                if ($value->type == Product::TYPE_CORRESPOND_LIST['video']) {            
                    $video = Video::where('id', $value->correspond_id)->get()->toArray();
                    $result = $generateService->generateVideos([], $video);
                    array_push($arr, $result[0]);
                }
            }
            $data = $arr;
        } else {
            $data = [];
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
        $checkRedisKey = self::KEY.":MemberOrderList:".$user_id.":".$page.":".$limit;
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
                        ->leftJoin('pays', 'orders.payment_type', 'pays.id')
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
                    $data[$key]['status_color'] = Order::STATUS_COLOR['yellow'];
                    break;
                case Order::ORDER_STATUS['delete']:
                    $data[$key]['status_color'] = Order::STATUS_COLOR['red'];
                    break;
                case Order::ORDER_STATUS['finish']:
                    $data[$key]['status_color'] = Order::STATUS_COLOR['black'];
                    break;
                case Order::ORDER_STATUS['failure']:
                    $data[$key]['status_color'] = Order::STATUS_COLOR['orange'];
                    break;
                default:
                    $data[$key]['status_color'] = Order::STATUS_COLOR['yellow'];
                    break;
            }
            $data[$key]['price'] = $order -> total_price;
            $data[$key]['pay_method'] =  $order -> pay_name ?? trans('api.member_control.coin_pay');
        }

        $this->redis->set($checkRedisKey, json_encode($data));
        $this->redis->expire($checkRedisKey, self::DAY);

        return $data;
    }

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
        $checkRedisKey = self::KEY.":MemberOrderList:".$user_id.":";
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

    // 判斷該帳號是否重複
    public function checkAccount($account)
    {
        if(Member::where('account', $account)->exists()){
            return false;
        }
        return true;
    }

    // Admin Account Search
    public function adminAccountSearch($account, $page, $pagePer)
    {
        // 撈取 
        return Member::select('*')->where('account', 'like', '%'.$account.'%')->offset(($page - 1) * $pagePer)->limit($pagePer)->orderBy('id', 'desc')->get();
    }

    // Admin Account Search Count
    public function adminAccountSearchCount($account): int
    {
        return Member::select('*')->where('account', 'like', '%'.$account.'%')->count();
    }
}
