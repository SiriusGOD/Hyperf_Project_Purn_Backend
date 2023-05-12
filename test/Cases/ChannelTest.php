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
use App\Model\Channel;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\RedeemService;
use App\Service\MemberRedeemService;
use App\Service\VideoService;
use Hyperf\Redis\Redis;
use App\Service\BaseService;

/**
 * @internal
 * @coversNothing
 */
class ChannelTest extends HttpTestCase
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

    public function testActorList()
    {
      $model = new Channel;
      // 发送端
      $data  = [
        'name'=>"test",
        'url'=>"test",
        'params'=>"test",
        'image'=>"test",
      ];
      $res = make(BaseService::class)->modelStore($model, $data);

      $this->assertSame('test', $res->name) ;
   }
}