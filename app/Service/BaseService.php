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

class BaseService
{
    // 是否允許IP
    public function allowIp($ip)
    {
        setLog()->info('record ip = ' . $ip);
        if (env('APP_ENV') == 'dev' || env('APP_ENV') == 'test' || env('APP_ENV') == 'local') {
//        if (env('APP_ENV') == 'dev' || env('APP_ENV') == 'test' ) {
            setLog()->info(' env APP_ENV true ' . env('APP_ENV'));
            return true;
        }
        if (! $ip) {
            setLog()->info('Line:' . __LINE__ . ' not deny ip ' . $ip);
            return false;
        }
        $allowIps = env('ALLOW_IP', ''); // 設定檔
        $allowIps = explode(',', $allowIps);
        // 如果是MD5 判斷
        $redisKey = 'allowIPMD5';
        $md5TXT = env('ALLOW_IP_MD5');

        if (! redis()->exists($redisKey)) {
            $client = make(\Hyperf\Guzzle\ClientFactory::class)->create();
            $res = $client->get($md5TXT);
            $statusCode = $res->getStatusCode();
            if ($statusCode == \App\Constants\ApiCode::OK) {
                $allowIpTxts = $res->getBody()->getContents();
                redis()->set($redisKey, $allowIpTxts, 60 * 60 * 6);
            }
        } else {
            $allowIpTxts = redis()->get($redisKey);
        }
        // 判斷md5 && 判斷IP 是否在 env內
        if (ipInArray(md5($ip), explode(',', $allowIpTxts)) || ipInArray($ip, $allowIps)) {
            setLog()->info(' Line:' . __LINE__ . ' md5 or ipInArray true ');
            return true;
        }
        setLog()->info('Line   ' . __LINE__ . ' ? - deny ip = ' . $ip);
        return false;
    }

    // 取得IP
    public function getIp($header, $params)
    {
        setLog()->info("...getHeaders = = \n " . json_encode($header));
        setLog()->info("...getServerParams = = \n " . json_encode($params));

        if (isset($header['x-forwarded-for'][0])) {
            $list = explode(',', $header['x-forwarded-for'][0]);
            return $list[0];
        }

        if (isset($header['remote_addr'][0])) {
            return $header['remote_addr'][0];
        }
        if (isset($header['x-real-ip'][0])) {
            return $header['x-real-ip'][0];
        }

        if (isset($params['remote_addr'])) {
            return $params['remote_addr'];
        }
        if (isset($params['x-real-ip'])) {
            return $params['x-real-ip'];
        }
        if (isset($params['http_client_ip'])) {
            return $params['http_client_ip'];
        }
        if (isset($params['http_x_real_ip'])) {
            return $params['http_x_real_ip'];
        }
        if (isset($params['http_x_forwarded_for'])) {
            // 部分CDN会获取多层代理IP，所以转成数组取第一个值
            $arr = explode(',', $params['http_x_forwarded_for']);
            return $arr[0];
        }
        return '';
    }

    // 共用儲存
    public function modelStore($model, array $datas): bool
    {
        if (isset($datas['id']) && ! empty($datas['id'])) {
            $model = $model->where('id', $datas['id'])->first();
        } else {
            $model = new $model();
        }
        foreach ($datas as $key => $val) {
            $model->{$key} = $val;
        }
        if ($model->save()) {
            return true;
        }
        return false;
    }
}
