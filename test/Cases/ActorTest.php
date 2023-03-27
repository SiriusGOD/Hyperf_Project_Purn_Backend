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

use PHPUnit\Framework\TestCase;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\ActorService;

/**
 * @internal
 * @coversNothing
 */
class ActorTest extends HttpTestCase
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

    public function testCount()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(ActorService::class);
        $res = $service->getCount();
        $data['id']=null;
        $data['user_id']=1;
        $data['name']=time();
        $data['sex']=1;
        $service->storeActor($data);
        $res2 = $service->getCount();
        $this->assertSame($res2, $res+1);
    }

    public function testApiList()
    {
        $res1 = $this->client->get('/api/actor/list');
        $this->assertSame(200, (int) $res1['code']);

        $res2 = $this->client->get('/api/actor/list',['page'=>2]);
        $this->assertSame(200, (int) $res2['code']);

        $this->assertNotSame($res2['data']["models"][0]["id"], $res1['data']["models"][0]["id"]);
    }

}
