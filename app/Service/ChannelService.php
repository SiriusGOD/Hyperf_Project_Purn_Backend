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
use Hyperf\DbConnection\Db;
use App\Model\ChannelAchievement;
use App\Model\ChannelRegister;
use Hyperf\Redis\Redis;
class ChannelService extends BaseService
{
    public const HOUR = 3720; 
    public const MEMBER_KEY = MemberService::KEY; 
    public const PAY_KEY = PayService::CACHE_KEY.":"; 
    public const CATE_MEMBER= "member"; 

    protected $redis;
    protected $channelAchievement;
    protected $channelRegister;

    public function __construct(Redis $redis, ChannelRegister $channelRegister, ChannelAchievement $channelAchievement)
    {
        $this->redis = $redis;
        $this->channelRegister =$channelRegister;
        $this->channelAchievement =$channelAchievement;
    }
    //每小時人數 // 每小時業積計算 從redis取
    public function calcChannelCount2DB(string $channel, int $channel_id ,$category)
    {
      $date = date("Ymd");
      $h = date("H");
      if($category=="member"){
        $mainKey = self::MEMBER_KEY;
        $field = "register_total";
      }else{
        $mainKey = self::PAY_KEY;
        $field = "amount";
      }

      $key = "$mainKey{$channel}:{$date}:{$h}";

      if ($this->redis->exists($key)) {
          $count = $this->redis->get($key);
          if($category == self::CATE_MEMBER){
            $data['total'] = $count;
          }else{
            $data['pay_amount'] = $count;
            $data['currency'] = "CNY";
          }
          $data['date'] = date("Y-m-d");
          $data['hour'] = $h;
          $data['channel_id'] = $channel_id;
          $data['channel'] = $channel;
          //寫入DB
          if($category=="member"){
              $this->modelStore($this->channelRegister,$data); 
          }else{
              $this->modelStore($this->channelAchievement,$data); 
          }
          //更新總數
          $this->incrementTotal($channel_id, $field ,$count);
      } 
    }

    //加總 到channel DB
    public function incrementTotal(int $channel_id, string $field, $num)
    {
        $query = "UPDATE channels SET {$field} = {$field} + :num WHERE id = :id";
        $bindings = [
            "num" => $num,
            'id' => $channel_id,
        ];
        $result = Db::connection()->statement($query, $bindings);
        return $result;
    } 

    //增加-記到redis
    public function setChannelRedis($channel ,$category , $amount = 0){
      $date = date("Ymd");
      $h = date("H");
      $parsedUrl = parse_url($channel);
      $domain = $parsedUrl['host'];

      if($category == SELF::CATE_MEMBER){
        $mainKe = self::MEMBER_KEY;
      }else{
        $mainKe = self::PAY_KEY;
      }

      $key = $mainKe . "{$domain}:{$date}:{$h}";
    
      if ($this->redis->exists($key)) {
        if($category == SELF::CATE_MEMBER){
          $this->redis->incr($key);
        }else{
           $total = $this->redis->get($key);
           $this->redis->set($key, $total+$amount);
        }
      } else {
        if($category == SELF::CATE_MEMBER){
          $this->redis->set($key, 1);
          $this->redis->expire($key, self::HOUR);
        }else{
          $this->redis->set($key, $amount);
          $this->redis->expire($key, self::HOUR);
        }
      }
    }
}
