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
use App\Service\ActorService;
/**
 * @internal
 * @coversNothing
 */
class ActorTest extends HttpTestCase
{
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

}
