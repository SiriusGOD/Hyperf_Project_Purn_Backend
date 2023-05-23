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
use Hyperf\Utils\Str;
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

    // 共用更新
    public function modelUpdate($model, array $keys, array $datas)
    {
        // 提取键的值
        $values = array_values($keys);

        // 构建 WHERE 条件
        $whereConditions = [];
        foreach ($keys as $key => $val) {
            $whereConditions[] = "$key = ?";
        }
        $whereClause = implode(' AND ', $whereConditions);

        // 组装更新语句
        $updateData = [];
        foreach ($datas as $column => $value) {
            $updateData[] = "$column = ?";
        }
        $updateClause = implode(', ', $updateData);

        // 构建参数数组
        $params = array_merge(array_values($datas), $values);

        // 执行更新
        $model->whereRaw($whereClause, $params)->update([$updateClause]);

        return $model;
    }
    // 共用儲存
    public function modelStore($model, array $datas)
    {
        if (isset($datas['id']) && ! empty($datas['id'])) {
            $model = $model->where('id', $datas['id'])->first();
            unset($datas['id']);
        } else {
            $model = new $model();
        }
        foreach ($datas as $key => $val) {
            $model->{$key} = $val;
        }
        if ($model->save()) {
            return $model;
        }
        return false;
    }

    // 查看redis是否存在
    public function chkRedis(string $key, array $where, $model, $redis ) : bool
    {
        if ($redis->exists($key)) {
            return true;
        }
        foreach ($where as $key => $val) {
            $model = $model->where($key,$val);
        }
        $model = $model->first();
        if ($model) {
            $redis->set($key, true);
            $redis->expire($key, 3600);
            return true;
        }else{
            return false;
        }
    }

    // 計算數量
    public function getCount($model)
    {
      return $model->count();
    }

    // 是否存在
    public function isExists($model,  $key, $val)
    {
      return $model->where($key, $val)->first();
    }

    // 共用取得
    public function get($model,  int $id)
    {
      return $model->where('id', $id)->first();
    }
    // 共用清單
    public function list($model, array $where, int $page, int $limit)
    {
        if(!empty( $where) ){
            foreach ($where as $key => $val) {
                $model = $model->where($key, $val);
            }
        }
        if ($page == 1) {
            $page = 0;
        }
        return $model->offset($page * $limit)->limit($limit)->get();
    }
    //那些 input 是必填
    public function isRequired($fieldsSetting)
    {
        $required = [];
        foreach ($fieldsSetting as $fiels => $setting) {
            if ($setting['required']) {
                $required[$fiels] =   $setting['required'] == 1 ? "required" : $setting['required'];
            }
        }
        return $required;
    }

    //如果寫入有檔案 
    public function fieldsHasFile($fieldsSetting)
    {
        $files = [];
        foreach ($fieldsSetting as $fiels => $setting) {
            if ($setting['type'] === 'file') {
                $files[$fiels] =   $setting['required'];
            }
        }
        return $files;
    }

    //如果寫入有ckeckbox 
    public function fieldsCheckbox($fieldsSetting, $request)
    {
        $ckeckbox = [];
        foreach ($fieldsSetting as $fiels => $setting) {
            if ($setting['type'] === 'checkbox') {
                if ($request->input($fiels)) {
                    $ckeckbox[$fiels] =  $request->input($fiels);
                } else {
                    $ckeckbox[$fiels] =   0;
                }
            }
        }
        return $ckeckbox;
    }
    //全部資料 
    public function collectData($inputAll, $inputChkbox, $imageData)
    {
        $array1 = array_merge($inputAll, $inputChkbox);
        if (empty($imageData)) {
            return $array1;
        } else {
            return array_merge($array1, $imageData);
        }
    }
    //如果寫入有system 必填
    public function fieldsSystem($fieldsSetting)
    {
        $system = [];
        foreach ($fieldsSetting as $fiels => $setting) {
            //if (!not_system($setting['type']) || $setting['required'] == true) {
            //    if ($fiels == 'user_id') {
            //        $system[$fiels] =  Auth::user()->id;
            //    } else {
            //        $system[$fiels] =  Auth::user()->$fiels;
            //    }
            //}
        }
        return $system;
    }

    //多圖片上傳
    public function multiImgService($request, $entity,  $fieldsSetting, $main)
    {
        $mainKey = 'files';
        if ($request->hasfile($mainKey)) {
            $type = $fieldsSetting[$mainKey]['association']['type'];
            $model = $fieldsSetting[$mainKey]['association'][$type];
            foreach ($request->file($mainKey) as $key => $file) {
                $name = $file->getClientOriginalName();
                $refPath = "/uploads/$main/$entity->id/";
                $path = '';//public_path() . $refPath;
                // $fileName = $path . $name;
                $file->move($path, $name);
                $imgRefence = new $model();
                $imgRefence->img = $refPath . $name;
                $imgRefence->model_type = $fieldsSetting[$mainKey]['association']['fromModel'];
                $imgRefence->model_id = $entity->id;
                $imgRefence->save();
            }
        }
    }

    //圖片上傳 回傳 CREATE格式 
    public function imgService($inputFiles, $request)
    {
        $inputData = [];
        foreach ($inputFiles as $fieldName => $requ) {
            if ($request->hasFile($fieldName)) {
                //$site_id = Str::random(10);
                //$imagePath = request($fieldName)->store("uploads/{$site_id}", 'public');
                //$image = Image::make(public_path("storage/{$imagePath}"));
                //$image->save(public_path("storage/{$imagePath}"), 60);
                //$image->save();
                //$inputData[$fieldName] = $imagePath;
            }
        }
        return $inputData;
    }
    /**
     * 搜尋處理
     */
    public function search($fieldsSetting, $request)
    {
        $all = $request->all();
        $search = [];
        $notInArray = ['page','direction','search','sort'];
        foreach (array_filter($all) as $name => $val) {

            // if ($name != 'page' && $name != "direction") {
            if(!in_array($name, $notInArray)){    
                $spliStr = explode("__", $name);
                if (strpos($name, "__") > 1) {
                    $name = $spliStr[0];
                }

                if (is_array($fieldsSetting[$name]['search']) && isset($val)) {

                    $level = $fieldsSetting[$name]['search']['level'];
                    if ($val !== 0) {

                        if ($level == 'equal') {
                            $search[] = [$name, '=', $val];
                        } elseif (count($spliStr) == 2) {
                            //時間區間                        
                            if ($spliStr[1] == 'start') {
                                $search[] = [$spliStr[0], ">=", "$val"];
                            } else {
                                $search[] = [$spliStr[0], "<=", "$val"];
                            }
                        } else {
                            $search[] = [$name, $level, "%$val%"];
                        }
                    }
                }
            }
        }
        return $search;
    }

    //存數據
    public function storeService($entity, $datas)
    {
        foreach ($datas as $key => $val) {
            $entity->$key = $val;
        }
        if ($entity->save()) {
            return true;
        } else {
            return false;
        }
    }
    
    //不要特定 的COL
    public function removeCol(array $datas ,string $colName):array
    {
      return array_map(function ($item) use ($colName) {
          unset($item[$colName]);
          return $item;
      }, $datas);
    }
}
