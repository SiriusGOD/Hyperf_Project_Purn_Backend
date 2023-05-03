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

use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\RedeemService;
use App\Service\MemberRedeemService;
use App\Service\VideoService;
use App\Service\EncryptService;
use App\Util\CRYPT;
use Hyperf\Redis\Redis;

use Hyperf\Utils\Codec\Json;

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

    public function testApiList()
    {
      // 发送端
      $key = env("APP_KEY");
      $signKey = env("SIGN_KEY");
      $data = ["page" => 1,'t'=>time()];
      // 將資料進行加密
      $encryptedData = openssl_encrypt(json_encode($data), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
      // 對加密後的資料進行簽名
      $signature = hash_hmac('sha256', $encryptedData, $signKey);
      // 設定請求的 header 和 body
      $headers = [
          'Content-Type' => 'application/json',
          'X-HMAC-Signature' => $signature, // 在 header 中加入簽名
      ];
      $body = [
          'data' => base64_encode($encryptedData), // 在 body 中加入加密後的資料（需進行 base64 編碼）
      ];
      // 發送請求
      $res = $this->client->post('/api/actor/list', $body, ['headers' => $headers]);
      $this->assertSame(200, (int)$res['code']);
   }

  public function testEnst()
  {
    $ser = new \App\Lib\Pinyin();
    $res=$ser::getPinyin("喔您u");
    print_r([$res]);
  }
}
