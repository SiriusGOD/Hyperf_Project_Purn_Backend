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

/**
 * @internal
 * @coversNothing
 */
class ActorApiTest extends HttpTestCase
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
        $countRes = $this->client->post('/api/actor/count');
        $count = (int)$countRes['data']["count"];
        if( $count > 10 ){
          $res1 = $this->client->post('/api/actor/list');
          $this->assertSame(200, (int) $res1['code']);
          $res2 = $this->client->post('/api/actor/list',['page'=>1]);
          $this->assertSame(200, (int) $res2['code']);
          $this->assertNotSame($res2['data']["models"][0]["id"], $res1['data']["models"][0]["id"]);
        }else{
          $this->assertSame(200, (int)$countRes['code']);
        }
   }

}
