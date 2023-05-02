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

use App\Util\CRYPT;

class EncryptService
{
    //api加密 
    public function hasPermission($callbacks ,$postData)
    {
      if (strpos($callbacks[0], "App\\Controller\\Api") !== false) {
        $data = json_encode($postData["data"]);
        $res_en = CRYPT::encrypt($data);
        $res_de = CRYPT::decrypt($data);
        print_r([$res_en]);
        print_r([$res_de]);
        return true;
      }
      return false;

    }
    
}
