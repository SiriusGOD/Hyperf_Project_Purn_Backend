<?php

namespace App\Util;

class Check
{
    //查看必填
    public static function require(array $inputs ,array $requires)
    {
      foreach($requires as $key ){
        if(empty($inputs[$key])){
            return $key;
        }
      }
      return false;
    }
}
