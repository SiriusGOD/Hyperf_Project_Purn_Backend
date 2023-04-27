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
use App\Model\Product;
use App\Service\MemberService;
use App\Model\Order;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ProxyServiceTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $memberService;
    protected $proxyService;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->proxyService = make(ProxyService::class);
    }
  
    public function testGenOrder()
    {
        $member = $this->proxyService->calcLevel(32,1);
        print_r(['wewer'=>$member]);
        $this->assertSame("11", '11');
    }


}
