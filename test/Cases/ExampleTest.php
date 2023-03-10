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

use HyperfTest\HttpTestCase;
use App\Service\ShareService;
use App\Model\Share;
use App\Model\ShareCount;
use Hyperf\Redis\Redis;
use Hyperf\Testing\Client;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends HttpTestCase
{
    public function testShareUri()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(ShareService::class);
        $sourceCount = Share::count();
        $ip = '127.1.2.3';
        $site_id = 3;
        $str = 12;
        $fingerprint = md5(date("Ymd") . $str);
        $res = $service->genUri($ip, $site_id, $fingerprint);
        $this->assertSame(200, $res['code']);
        $service->insertShareData($ip, $site_id, $fingerprint, $res['share_code']);
        $lastCount = Share::count();
        echo "$sourceCount // $lastCount \n";
        $this->assertSame($sourceCount, $lastCount);
    }

    /**
     * 分享網址點擊 API TEST
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \RedisException
     */
    public function testClickShareUri()
    {
        $shareCount = ShareCount::count();
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(ShareService::class);
        $ip = '127.1.2.3';
        $site_id = 3;
        $shareCode = 'e05a2b3ae181d3884a4de96267753300';
        $res = $service->insertClickData($ip, $site_id, $shareCode);

        $client = make(Client::class);
        $result = $client->get('/api/share/click?site_id=1&share_code=123123');
        print_r($result);
        $this->assertSame(200, $res['code']);
    }


    public function testDay()
    {
        $shareCount = ShareCount::count();
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(ShareService::class);
        $res = $service->getTtl();
        $this->assertSame(200, $res['code']);
    }


    public function testShare()
    {
        $shareCount = ShareCount::count();
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(ShareService::class);
        $ip = '127.1.12.3';
        $site_id = 1;
        $shareCode ='123123123qweqwe';
        $res = $service->insertClickData($ip, $site_id, $shareCode);
        print_r($res);
        $this->assertSame(200, $res['code']);
    }
}
