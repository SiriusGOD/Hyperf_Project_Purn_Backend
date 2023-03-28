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
namespace App\Util;


class URand
{
    public static $RAND_TAGS = ['3P','NTR','初体验','制服','办公室','反转','同居','大尺度','女大生','好友','妹妹','姐妹','姐姐','学校','小混混','小说改 ','少女','巨乳','强迫','戏剧','报仇','推荐','有夫之 ','有妇之 ','校园','欲望','正妹','浪漫','狗血剧','畅销作 ','老少配','肉欲','观淫','调教','野外露 ','高跟鞋','黑丝袜'];
    public static $RAND_ACTORS = ['杨紫','素人','JVID','迪力热巴','赵丽颖','范冰冰','杨颖','杨超越','古力娜扎','关晓彤','李沁','刘亦菲','宋轶','佟丽娅'];
    public static $RAND_TITLES = ['唐朝肚兜的美感','蓝色情趣内衣的极品诱惑1','黑色情趣内衣的极品诱惑','台球桌上的巨乳美人诱惑','性感女皇的大尺度写真','蓝色情趣内衣的极品诱惑2','黑色镂空性感写真秀','小皮鞭的自慰秀','高跟黑色镂空写真','性感女皇的镂空自慰秀','小护士的性格写真秀','性感小熟妇的大尺度写真','白丝性感写真诱惑','小熟妇的板凳舞','唐朝肚兜的泳池诱惑','性感女皇的皮带调教','办公室的OL诱惑','衬衣的揉乳诱惑','巨乳女神的泳池诱惑','性感女皇的自慰诱惑','性感熟妇的黑丝情趣内衣','学生制服的诱惑','白色连体丝袜','高跟绝世美腿']; 
    public $DATAS = [];

    public static function randomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charLength - 1)];
        }
        return $randomString;
    }

    public static function randomInt($min = 0, $max = 100) {
      return rand($min, $max);
    }
    //隨機title 
    public static function getRandTitle(){
      return self::$RAND_TITLES[rand(0 , count(self::$RAND_TITLES) -1)];
    }

    //取得 測試 演員
    public static function getRandTagActor(int $nums, string $type){
      $datas = [];  
      if (in_array($type, ['ACTOR', 'TAG'])) {
        while (count($datas) < $nums) {
          if ($type == "ACTOR") {
            $row = self::$RAND_ACTORS[rand(0, count(self::$RAND_ACTORS) - 1)];
          } else if ($type == "TAG") {
            $row = self::$RAND_TAGS[rand(0, count(self::$RAND_TAGS) - 1)];
          }
          if (!in_array($row, $datas)) {
            $datas[] = $row;
          }
        }
        return implode(",", $datas); 
      }
    }

    //亂數 tag
    public static function getRandTag(array $tags, int $count){
      $datas = [];  
      while (count($datas) < $count) {
        $rand = rand(0, count($tags));
        if (!in_array($rand, $datas)) {
          $datas[$rand] = $rand;
        }
      }
      return $datas;
    }
}
