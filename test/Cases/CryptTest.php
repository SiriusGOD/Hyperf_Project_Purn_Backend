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
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\RedeemService;
use App\Service\MemberRedeemService;
use App\Service\VideoService;
use App\Util\CRYPT;
use Hyperf\Redis\Redis;
use App\Service\MemberService;

/**
 * @internal
 * @coversNothing
 */
class CryptTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $redeem;
    protected $memberRedeem;
    protected $video;
    protected $redis;
  
    protected $testUserId = 1;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->redeem = make(RedeemService::class);
        $this->memberRedeem = make(MemberRedeemService::class);
        $this->video = make(VideoService::class);
        $this->redis = make(Redis::class);
    }

    public function testEnc()
    {
      $str = '{"t":"1683190025267","device_id":"wAztqqqqtOD32221","device":"ios"}';
      $data1 = CRYPT::encrypt($str); // 在 body 中加入加密後的資料（需進行 base64 編碼）
      $data2 = CRYPT::decrypt($data1); // 在 body 中加入加密後的資料（需進行 base64 編碼）
      $this->assertSame($data2, $str);
    }

    public function testActorList()
    {
      // 发送端
      $user = Member::first();
      $token = auth()->login($user);
      make(MemberService::class)->saveToken($user->id, $token);
      //加密前用json
      $str = '{"t":"1683190025267","device_id":"az21qr1qerqL2qq2AptOD14","device":"ios","aff_url":"https://dotblogs.com.tw/momoBear/2020/02/27/143725"}';
      //第一次加密後

      $headers = [
          'Content-Type' => 'application/json',
          //'X-HMAC-Signature' => base64_encode($signature), // 在 header 中加入簽名
      ];
      if (env('PARAMS_ENCRYPT_FLAG')) {
          $body = [
              'data' => CRYPT::encrypt($str), // 在 body 中加入加密後的資料（需進行 base64 編碼）
          ];
          $body = [
              'data' => 'mlRo487bb5LiQTxRZ0wJ+4HvFa+9QQLI3rATKk7hNiMyK7Zf+0qh4npdYHSfawX1D9MbPY/g5JRF7tQYC49aYacWh2tuk4PYQ7/CkSTh/TlC8G8OZx/n/i2AzLfUK03LoPz2OxyYzFOMcJt0WkzW98fsO+gNFFCpV9voxeoCT+8=', // 在 body 中加入加密後的資料（需進行 base64 編碼）
          ];
          print_r($body);
      } else {
          $body =  json_decode($str,true); 
      }
      // 發送請求
      $res = $this->client->post('/api/member/login',
          $body,
          [
              'Authorization' => 'Bearer ' . $token,
              'headers' => $headers
          ]
      );
      print_r([$res, '2result' ]);
      $this->assertSame(200, (int)$res['code']);
   }
}
