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
namespace HyperfTest\Cases;

use App\Model\Member;
use App\Model\Redeem;
use App\Model\BuyMemberLevel;
use App\Service\RedeemService;
use App\Service\MemberRedeemService;
use App\Service\MemberService;
use App\Service\VideoService;
use Hyperf\Utils\Str;
use Hyperf\Redis\Redis;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use Carbon\Carbon;

/**
 * @internal
 * @coversNothing
 */
class RedeemApiTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $redeem;
    protected $memberRedeem;
    protected $video;
    protected $redis;
    protected $token;
    protected $loggerFactory;
    protected $logger;
  
    protected $testUserId = 1;

  public function __construct($name = null, 
                              array $data = [], 
                              $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->redeem = make(RedeemService::class);
        $this->memberRedeem = make(MemberRedeemService::class);
        $this->video = make(VideoService::class);
        $this->redis = make(Redis::class);
    }

    //使用者沒有兌換卷
    public function testMemberCheck()
    {
      $currentDate = date('Y-m-d H:i:s'); // 当前日期时间
      $nextMonth = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($currentDate))); // 加一月后的日期时间

      $rdata = [
        'title' => 'VIP a day',
        'code'  => Str::random(8),
        'count' =>10 ,
        'counted'=>0,
        'category_name'=>'VIP',
        'diamond_point'=>0,
        'category_id'=>1,
        'vip_days'=>1,
        'free_watch'=>0,
        'status'=>0,
        'start' => date('Y-m-d H:i:s'),
        'end' => $nextMonth ,
        'content' => 'test',
      ];

      $model=new Redeem();
      foreach($rdata as $k =>$v){
        $model->$k = $v;
      }
      $model->save();
      $user = Member::orderBy('id','desc')->first();
      $token = auth()->login($user);
      $this->token = $token; 
      make(MemberService::class)->saveToken($user->id, $token);
      $r = Redeem::orderBy('id','desc')->first();
      $json["code"] = $r->code;
      $data = $this->client->post('/api/redeem/check_redeem',$json , [
          'Authorization' => 'Bearer ' . $token,
      ]);
      $this->assertSame(200, (int)$data['code']);
      $this->assertIsString($data['data']['product_name']);
    }
    
     //註冊2級代理
    public function creatMmember()
    {
        $insertArray = self::memberExp();
        $q = "s_".date("YmdHis")."_".rand(11,99);
        $insertArray["name"] = $q;
        $insertArray["email"] =$q."@example.com";
        $insertArray["device_id"] = md5((string)Str::random(10).time().date('YmdHis') );
        $this->client->post('/api/member/login', $insertArray);
    }

    public function memberExp(){
        $domains = ["http://love.com/?aff_code=qwe", "http://sex.com/?aff_code=qwe" , "http://sex8.com/?aff_code=qwe", "http://xvideo.com/?aff_code=qwe"];
        return  [
            'name' => 'John',
            'password' => 'a123456',
            'email' => 'john@example.com',
            'member_level_status' => 1,
            'device' => 'ios',
            'invited_by' => 0,
            'invited_num' => 0,
            'tui_coins' => 0.00,
            'total_tui_coins' => 0.00,
            'aff_url' => $domains[rand(0,3)],
        ];
    }

    //兌換CODE VIP 二次
    public function testRedeemCodeByVipTwch()
    {
      $select =['id','member_level_status','vip_quota'];
      self::creatMmember();
      $user = Member::select($select)->orderBy('id','desc')->first();
      $memberId =$user->id;
      $token = auth()->login($user);
      $this->token = $token; 
      make(MemberService::class)->saveToken($user->id, $token);

      $redeem = Redeem::where("vip_days",1)->orderBy('id', 'desc')->offset(0)->limit(1)->first();
      $data = $this->client->post('/api/redeem/redeemCode',
        ["code" => $redeem->code ], 
        ['Authorization' => 'Bearer ' . $token ]
      );
      $newuser = Member::select($select)->orderBy('id','desc')->first();

      $this->assertSame((int)Member::VIP_QUOTA['DAY'], (int)$newuser->vip_quota);
      $redeem = Redeem::where("vip_days",1)->orderBy('id', 'desc')->offset(1)->limit(1)->first();
      $data = $this->client->post('/api/redeem/redeemCode',
        ["code" => $redeem->code ], 
        ['Authorization' => 'Bearer ' . $token ] 
      );
      $buy = BuyMemberLevel::where('member_id',$memberId)->first();

      $newuser = Member::select($select)->orderBy('id','desc')->first();

      $this->assertSame((int)Member::VIP_QUOTA['UP_TWO'], (int)$newuser->vip_quota);
      $this->assertSame($buy->member_id , $user->id);

      $date1 = Carbon::parse($buy->end_time);
      $date2 = Carbon::parse($buy->start_time);

      $diffInDays = $date2->diffInDays($date1);
      //算是不是二天
      $this->assertSame(2 , $diffInDays);
      $this->assertSame(200, $data['code']);
    }

    //取可用兌換卷亂輸入
    public function testUsedErrorCode()
    {
      self::creatMmember();
      $user = Member::orderBy('id','desc')->first();
      $token = auth()->login($user);
      $this->token = $token; 
      make(MemberService::class)->saveToken($user->id, $token);
      $data = $this->client->post('/api/redeem/redeemCode',
      [
        "code" => Str::random(10)
      ], 
      [
          'Authorization' => 'Bearer ' . $token,
      ]);
      $this->assertSame('优惠卷不存在或己過期', $data['data']['msg'] );
    }
}
