<?php

namespace HyperfTest\Cases;

use App\Model\User;
use App\Service\UserService;
use App\Task\ProductTask;
use HyperfTest\HttpTestCase;
use PHPUnit\Util\Json;

class AdvertisementTest extends HttpTestCase
{
    public function testList()
    {
        $data = $this->client->get('/api/advertisement/list');

        $this->assertSame(200, (int)$data['code']);
    }
}