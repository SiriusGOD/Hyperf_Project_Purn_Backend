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
use App\Model\Member;
use App\Service\PayService;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class PayUrlTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $payService;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->payService = make(PayService::class);
    }

    //我的收益
    public function testPayUrl()
    {
        $url = "http://pay.hyys.info/v1/payments";
        $key = "d2bf7126723ea8f6005ba141ea3c3e2c";
        $data = array(
            "app_name" => "sexfun",
            "app_type" => "ios",
            "aff" => "CwT7z:18",
            "amount" => "30.00"
        );
        $sign = $this->payService->make_sign_pay($data, $key);
        $data['sign'] = $sign;
        $data['ip'] = "143.42.65.93";
        $data['pay_type'] = "alipay";
        $data['type'] = "online";
        $data['is_sdk'] = '0';
        $data['product'] = "vip";
        $result = $this->payService->curlPost($url, $data);
        print_r($result);
        $this->assertSame(true, $result['success']);
    }
}
