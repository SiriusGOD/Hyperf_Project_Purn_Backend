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
namespace App\Service;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class UploadService
{
    public const CACHE_KEY = 'curl';

    public Redis $redis;

    public $log;

    protected $curlVerbose = false;

    public function __construct(Redis $redis, LoggerFactory $factory)
    {
        $this->redis = $redis;
        $this->log = $factory->get('default');
    }

    public function postJson($url = '', array $data = [], $timeout = 30)
    {
        $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);
        // $data_string = $data;
        $curl_con = curl_init();
        curl_setopt($curl_con, CURLOPT_URL, $url);
        curl_setopt($curl_con, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_con, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_con, CURLOPT_HEADER, false);
        curl_setopt($curl_con, CURLOPT_POST, true);
        curl_setopt($curl_con, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_con, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt(
            $curl_con,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)]
        );
        curl_setopt($curl_con, CURLOPT_POSTFIELDS, $data_string);
        $res = curl_exec($curl_con);
        // var_export($res);die;
        $status = curl_getinfo($curl_con);
        // var_export($status);die;
        curl_close($curl_con);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            return $res;
        }
        print_r($res);
        errLog('error:' . var_export($res, true));
        return false;
    }

    /**
     * post.
     * @param mixed $timeout
     * @param mixed $url
     * @param mixed $data
     * @param mixed $header
     * @return bool|mixed|string
     */
    public static function post($url, $data, $header = [], $timeout = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        if (! empty($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($curl);

        if (curl_errno($curl)) {
            errLog(curl_error($curl));
            return 'false';
        }
        $resultJson = json_decode($result, true);
        return ($resultJson === null) ? $result : $resultJson;
    }

    // *** post
    public function curlPost($url, $params = [], $timeout = 30)
    { // 模拟提交数据函数
        $post = htmlspecialchars_decode(! empty($params) ? http_build_query($params) : '');
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // 可post多维数组

        $result = curl_exec($ch);
        // print_r($result);die;
        /* if($result === false) {
             echo 'Curl error: ' . curl_error($ch);
         }*/
        curl_close($ch);
        return $result;
    }

    public function request($url, $params = [], $header = [])
    {
        return $this->deleteMp4($url, $params, $header);
    }

    public function deleteMp4($url, $params = [], $header = [])
    { // 模拟提交数据函数
        /*
         * $header = array (
            "Content-Type:application/json",
            "Content-Type:x-www-form-urlencoded",
            "Content-type: text/xml",
            "Content-Type:multipart/form-data"
        )*/

        // 启动一个CURL会话
        $ch = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // 忽略证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->curlVerbose);
        if (! empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        // 发送一个常规的POST请求。
        $str = is_array($params) ? http_build_query($params) : $params;
        $str = str_replace('amp;', '', $str);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str); // 可post多维数组
        // curl_setopt($ch, CURLOPT_HEADER,0);//是否需要头部信息（否）
        // 执行操作
        $result = curl_exec($ch);
        curl_close($ch);
        # 返回数据
        return $result;
    }

    /**
     * 上传图片到图片服务器.
     * @param string $id 唯一标识
     * @param string $imgPath 图片路径
     * @param string $position 存放位置 actors,ads,av,head,icons,lusir,pay,upload,xiao,youtube,im
     * @param string $remoteUrl 服务器上传url地址
     * @param string $_id 番号
     * @return array {code:1,msg:"09159db1a99acb773ecf8490c01973ee.jpeg"}
     * @throws \Exception
     */
    public function upload2Remote($id, $imgPath, $position, $remoteUrl = null, $_id = '')
    {
        if ($remoteUrl === null) {
            $remoteUrl = env('IMAGE_UPLOAD', 'https://new.ycomesc.live/imgUpload.php');
        }
        $cover = curl_file_create(realpath($imgPath), mime_content_type($imgPath));
        if ($position == 'ads') {
            $id .= time() . mt_rand(1, 999);
        }
        $data = [
            'id' => $id,
            '_id' => $_id,
            'position' => $position,
        ];
        $img_key = env('IMAGE_KEY', '132f1537f85scxpcm59f7e318b9epa51');
        $sign = $this->make_sign($data, $img_key);
        $data['cover'] = $cover;
        $data['sign'] = $sign;
        $result = self::post($remoteUrl, $data);
        $this->log->info('upload image result : ' . json_encode($result));

        return $result;
    }

    # 签名
    public function make_sign($array, $signKey)
    {
        if (empty($array)) {
            return '';
        }
        ksort($array);
        // $string = http_build_query($array);

        $arr_temp = [];
        foreach ($array as $key => $val) {
            $arr_temp[] = $key . '=' . $val;
        }
        $string = implode('&', $arr_temp);

        $string = $string . $signKey;

        # 先sha256签名 再md5签名
        return md5(hash('sha256', $string));
    }
}
