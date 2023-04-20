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
use App\Constants\ProxyCode;
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

    public function testApiList()
    {
        $money = 1000;
        //$countRes = $this->client->get('/api/actor/count');
        //$count = (int)$countRes['data']["count"];
        foreach(ProxyCode::COIN_RATE as $key => $data){
          if( $money < ProxyCode::COIN_RATE[$key]["money"] ){

          }elseif($money > ProxyCode::COIN_RATE[$key]["money"] ){

          } 
        }
        //$this->assertSame(200, (int) $res2['code']);
   }

}
