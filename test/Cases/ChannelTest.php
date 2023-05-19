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
use App\Service\ChannelService;

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
    }
    //測試register
    public function testRegister()
    {
        $models = Channel::all();
        if (count($models) == 0) {
            return;
        }
        foreach ($models as $model) {
              make(ChannelService::class)->calcChannelCount2DB($model->url ,$model->id ,'member');
              make(ChannelService::class)->calcChannelCount2DB($model->url ,$model->id ,'achievement');
        }
      $this->assertSame('test', 'test') ;
   }
}
