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
use App\Service\CurlService;
use App\Service\MemberRedeemService;
use Hyperf\Redis\Redis;
/**
 * @internal
 * @coversNothing
 */
class CentImgTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $redeem;
    protected $memberRedeem;
    protected $crypt;
    protected $curl;
    protected $redis;
  
    protected $testUserId = 1;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->memberRedeem = make(MemberRedeemService::class);
        $this->curl = make(CurlService::class);
        $this->redis = make(Redis::class);
    }

    public function testCry()
    {
        $url = "https://new.ycomesc.live/imgUpload.php";
        //uploadMp42Remote($uuid, $filePath, $remoteUrl = null)
        $filePath="/tmp/a.jpg";
        $filePath="/var/www/public/advertisement/a.jpg";
        $uuid = 'e2a144721e9e6cbcc4855217de1cb94f';
        // $position = 'ads';
        $position = 'actor';
        $res= $this -> curl -> upload2Remote($uuid, $filePath, $position, $url);
        // print_r($res['msg']);
        
        // $baseUrl = 'https://images.91tv.tv/';
        // $baseUrl = "https://images.91tv.tv/img.actors/";
        $baseUrl = env('VIDEO_THUMB_URL');

        var_dump($this->url_resource($res['msg'], $baseUrl));
        $this->assertSame(true,$res);
    }

    public function url_resource($url, $baseUrl)
    {
        if (empty($url)) {
            return $url;
        }
        if (strpos($url, '://') !== false) {
            return $url;
        }
        //老项目，新路经兼容问题
        $url_replace['/img.ads/'] = '';
        $url_replace['/img.xiao/'] = '';
        $url_replace['/img.head/'] = '';
        $url_replace['/img.upload/'] = '';
        $url_replace['/img.im/'] = '';
        //新路经兼容的目录
        $check_path_1 = 'new/';
        $check_path_2 = 'upload/';
        $check_path_3 = '/img.gv';
        //兼容替换处理
        if (stripos($url, $check_path_1) !== false || stripos($url, $check_path_2) !== false || stripos($url, $check_path_3) !== false) {
            $baseUrl = str_ireplace(array_keys($url_replace), array_values($url_replace), $baseUrl);
        }

        $baseUrl = rtrim($baseUrl, '/');
        $url = ltrim($url, '/');
        return $baseUrl . '/' . $url;
    }
}
