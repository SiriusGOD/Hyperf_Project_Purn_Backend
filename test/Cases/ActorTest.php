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
        $this->assertSame(17, $res);
    }

    public function testOffset()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(ActorService::class);
        $res = $service->getActors(0,2);
        $this->assertSame(2, count($res));
    }

}
