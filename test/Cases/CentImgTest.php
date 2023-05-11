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
use App\Service\CurlService;
use App\Service\MemberRedeemService;
use Hyperf\Redis\Redis;
/**
 * @internal
 * @coversNothing
 */
class CentImgTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $redeem;
    protected $memberRedeem;
    protected $crypt;
    protected $curl;
    protected $redis;
  
    protected $testUserId = 1;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->memberRedeem = make(MemberRedeemService::class);
        $this->curl = make(CurlService::class);
        $this->redis = make(Redis::class);
    }

    public function testCry()
    {
        $url = "https://new.ycomesc.live/imgUpload.php";
        //uploadMp42Remote($uuid, $filePath, $remoteUrl = null)
        $filePath="/tmp/a.png";
        $filePath="/var/www/public/advertisement/a.png";
        $uuid = 'e2a144721e9e6cbcc4855217de1cb94f';
        $res=CurlService::uploadMp42Remote($uuid, $filePath, $url);
        print_r([$res]);
        $this->assertSame(true,$res);
    }
}
