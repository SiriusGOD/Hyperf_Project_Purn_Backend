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
use App\Util\CRYPT;
use Hyperf\Redis\Redis;
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
        $data = ["page"=>1];
        $data = ["page"=>CRYPT::encrypt(json_encode($data) )];
        print_r($data);
        $res1 = $this->client->get('/api/actor/list',$data);
        
        $this->assertSame(200, (int) $res1['code']);
   }
}
