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
use App\Constants\ProxyCode;

/**
 * @internal
 * @coversNothing
 */
class ProxyRateTest extends HttpTestCase
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

    public function getRateExp($money, $rate, $userLevel)
    {
        $uRate = ProxyCode::LEVEL[$userLevel]['rate'];
        $service = make(ProxyService::class);
        $res = $service->returnRateMoney($money ,$userLevel); 
        $this->assertSame(number_format($res,2), number_format($money * $uRate *  $rate ,2) );
    }

    public function testApiList100500()
    {
      $money = 100500;
      self::getRateExp($money, 0.3, 1);
      self::getRateExp($money, 0.3, 2);
      self::getRateExp($money, 0.3, 3);
      self::getRateExp($money, 0.3, 4);
    }

    public function testApiList70500()
    {
      $money = 70500;
      self::getRateExp($money, 0.26, 1);
      self::getRateExp($money, 0.26, 2);
    }

    public function testApiList40500()
    {
      $money = 40500;
      self::getRateExp($money, 0.23, 1);
      self::getRateExp($money, 0.23, 2);
    }

    public function testApiList20500()
    {
      $money = 20500;
      self::getRateExp($money, 0.2, 1);
      self::getRateExp($money, 0.2, 2);
    }

    public function testApiList10500()
    {
      self::getRateExp(10500, 0.18, 1);
      self::getRateExp(10500, 0.18, 2);
    }

    public function testApiList10000()
    {
      self::getRateExp(10000, 0.16, 1);
      self::getRateExp(10000, 0.16, 2);
    }

    public function testApiList6000()
    {
      self::getRateExp(6000, 0.16, 1);
      self::getRateExp(6000, 0.16, 2);
      self::getRateExp(6000, 0.16, 3);
      self::getRateExp(6000, 0.16, 4);
    }

    public function testApiList5000()
    {
      self::getRateExp(5000, 0.14, 1);
      self::getRateExp(5000, 0.14, 2);
      self::getRateExp(5000, 0.14, 3);
      self::getRateExp(5000, 0.14, 4);
    }


    public function testApiList4000()
    {
      self::getRateExp(4000, 0.14, 1);
      self::getRateExp(4000, 0.14, 2);
      self::getRateExp(4000, 0.14, 3);
      self::getRateExp(4000, 0.14, 4);
    }

    public function testApiList3000()
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

    public function testApiList2000()
    {
        $money = 2000;
        $rate = 0.12;
        $service = make(ProxyService::class);
        $res = $service->returnRateMoney($money ,1); 
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
