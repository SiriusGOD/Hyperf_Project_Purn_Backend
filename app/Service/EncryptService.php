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
class EncryptService
{
    //api加密 
    public function hasPermission($callbacks,$postData,$signature,$expectedSignature ,$decryptedData )
    {
      if (strpos($callbacks[0], "App\\Controller\\Api") !== false) {
        if ($signature !== $expectedSignature) {
          // 簽名不匹配，可能是請求被篡改，拒絕該請求
          return response()->json(['error' => 'Invalid signature'], 401);
        }
        return true;
      }
      return false;
    }
    
}
