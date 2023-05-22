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

use App\Model\Channel;
use App\Model\ChannelAchievement;
use App\Model\ChannelRegister;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;

class ChannelService extends BaseService
{
    public const HOUR = 3720;

    public const MEMBER_KEY = MemberService::KEY;

    public const PAY_KEY = PayService::CACHE_KEY . ':';

    public const CATE_MEMBER = 'member';

    protected $redis;

    protected $channelAchievement;

    protected $channelRegister;

    protected $channel;

    public function __construct(Redis $redis, Channel $channel, ChannelRegister $channelRegister, ChannelAchievement $channelAchievement)
    {
        $this->redis = $redis;
        $this->channel = $channel;
        $this->channelRegister = $channelRegister;
        $this->channelAchievement = $channelAchievement;
    }

    // 每小時人數 // 每小時業積計算 從redis取
    public function calcChannelCount2DB(string $channel, int $channel_id, $category)
    {
        $date = date('Ymd');
        // 获取当前时间
        $currentDateTime = Carbon::now();
        // 减一小时
        $oneHourEarlier = $currentDateTime->subHour();
        // 获取小时（24小时制）
        $h = $oneHourEarlier->format('H');
        $h = date('H');
        if ($category == 'member') {
            $mainKey = self::MEMBER_KEY;
            $field = 'register_total';
        } else {
            $mainKey = self::PAY_KEY;
            $field = 'amount';
        }

        $key = "{$mainKey}{$channel}:{$date}:{$h}";

        if ($this->redis->exists($key)) {
            $count = $this->redis->get($key);
            if ($category == self::CATE_MEMBER) {
                $data['total'] = $count;
            } else {
                $data['pay_amount'] = $count;
                $data['currency'] = 'CNY';
            }
            $data['date'] = date('Y-m-d');
            $data['hour'] = $h;
            $data['channel_id'] = $channel_id;
            $data['channel'] = $channel;
            // 寫入DB
            if ($category == 'member') {
                $this->modelStore($this->channelRegister, $data);
            } else {
                $this->modelStore($this->channelAchievement, $data);
            }
            // 更新總數
            $this->incrementTotal($channel_id, $field, $count);
        }
    }

    // 加總 到channel DB
    public function incrementTotal(int $channel_id, string $field, $num)
    {
        $query = "UPDATE channels SET {$field} = {$field} + :num WHERE id = :id";
        $bindings = [
            'num' => $num,
            'id' => $channel_id,
        ];
        return Db::connection()->statement($query, $bindings);
    }

    // 增加-記到redis
    public function setChannelRedis($channel, $category, $amount = 0)
    {
        $date = date('Ymd');
        $h = date('H');
        $parsedUrl = parse_url($channel);
        $domain = $parsedUrl['host'];
        self::parseDomain($channel);
        if ($category == self::CATE_MEMBER) {
            $mainKe = self::MEMBER_KEY;
        } else {
            $mainKe = self::PAY_KEY;
        }

        $key = $mainKe . "{$domain}:{$date}:{$h}";

        if ($this->redis->exists($key)) {
            if ($category == self::CATE_MEMBER) {
                $this->redis->incr($key);
            } else {
                $total = $this->redis->get($key);
                $this->redis->set($key, $total + $amount);
            }
        } else {
            if ($category == self::CATE_MEMBER) {
                $this->redis->set($key, 1);
                $this->redis->expire($key, self::HOUR);
            } else {
                $this->redis->set($key, $amount);
                $this->redis->expire($key, self::HOUR);
            }
        }
    }

    // 統計查詢
    public function calcTotal(string $date,int $channel_id) : array
    {
      $ex_date = explode(" - ",$date);
      $sDate = \Carbon\Carbon::parse(trim($ex_date[0]));
      $eDate = \Carbon\Carbon::parse(trim($ex_date[1]));
      $wheres = [
          ['date',">=",$sDate->toDateString()],
          ['date',"<=",$eDate->toDateString()],
          ['hour',">=",$sDate->format('H')],
          ['hour',"<=",$eDate->format('H')],
          ['channel_id',"=",$channel_id],
      ];
      $reg_total = $this->channelRegister->where($wheres )->sum("total");
      $ach_total = $this->channelAchievement->where($wheres )->sum("pay_amount");
      return ['register_total'=>$reg_total , 'ach_total'=>$ach_total]; 
    }

    // 產生新的渠道
    public function parseDomain(string $url)
    {
        $parsedUrl = parse_url($url);
        $insert['name'] = $parsedUrl['host'];
        $insert['url'] = $parsedUrl['host'];
        $insert['params'] = $parsedUrl['query'];
        $insert['image'] = '';
        $res = $this->isExists($this->channel, 'url', $parsedUrl['host']);
        if (empty($res->id)) {
            $this->modelStore($this->channel, $insert);
        }
    }

    //取得渠道
    public function getChannel($id)
    {
        return $this->isExists($this->channel , 'id',$id);
    }

    //渠道總數
    public function thisCont()
    {
        return Channel::count();
    }

    //渠道分頁
    public function getChannels($page = 0, $limit = 0)
    {
        return Channel::offset(($page - 1) * $limit)
                            ->limit($limit)
                            ->orderBy('id', 'desc')->get();

    }

}
