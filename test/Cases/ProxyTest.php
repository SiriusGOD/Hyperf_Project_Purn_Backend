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
use App\Service\ProxyService;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ProxyTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
    }

    public function testApiList300()
    {
        $money = 3000;
        $rate = 0.14;
        $service = make(ProxyService::class);
        $res = $service->returnRateMoney($money ,1); 

        $this->assertSame(number_format($res,2), number_format($money *  $rate ,2) );

        $res = $service->returnRateMoney($money ,2); 
        $this->assertSame(number_format($res,2), number_format($money * 0.25 *  $rate ,2) );

        $res = $service->returnRateMoney($money ,3); 
        $this->assertSame(number_format($res,2), number_format($money * 0.15 *  $rate ,2) );

        $res = $service->returnRateMoney($money ,4); 
        $this->assertSame(number_format($res,2), number_format($money * 0.1 *  $rate ,2) );
    }

    public function testApiList200()
    {
        $money = 2000;
        $rate = 0.12;
        $service = make(ProxyService::class);
        $res = $service->returnRateMoney($money ,1); 
        print_r( [number_format($res,2), number_format($money *  $rate ,2)] );
        $this->assertSame((float)$res, (float)$money * $rate);

        $res = $service->returnRateMoney($money ,2); 
        $this->assertSame((float)$res, (float)$money * 0.25 * $rate);

        $res = $service->returnRateMoney($money ,3); 
        $this->assertSame((float)$res, (float)$money * 0.15 * $rate);

        $res = $service->returnRateMoney($money ,4); 
        $this->assertSame((float)$res, (float)$money * 0.1 * $rate);
    }

    public function testApiList()
    {
        $money = 1000;
        $service = make(ProxyService::class);
        $res = $service->returnRateMoney($money ,1); 
        $this->assertSame((float)100, $res);

        $res = $service->returnRateMoney($money ,2); 
        $this->assertSame((float)25, $res);

        $res = $service->returnRateMoney($money ,3); 
        $this->assertSame((float)15, $res);

        $res = $service->returnRateMoney($money ,4); 
        $this->assertSame((float)10, $res);
   }

}
