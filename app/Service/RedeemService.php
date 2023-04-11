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

use App\Model\MemberRedeemVideo;
use App\Model\MemberRedeem;
use App\Model\Redeem;

use Hyperf\Database\Model\Collection;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class RedeemService
{
    public const CACHE_KEY = 'redeem';
    public const EXPIRE = 600;
    public const COUNT_EXPIRE = 180;

    protected $redis;
    protected $logger;
    protected $redeem;
    protected $memberRedeem;
    protected $memberRedeemVideo;

  public function __construct(Redis $redis, 
    LoggerFactory $loggerFactory, 
    Redeem $redeem, 
    MemberRedeem $memberRedeem,
    MemberRedeemVideo $memberRedeemVideo)
    {
        $this->logger = $loggerFactory->get('reply');
        $this->redis = $redis;
        $this->redeem = $redeem;
        $this->memberRedeem = $memberRedeem;
        $this->memberRedeemVideo = $memberRedeemVideo;
    }

    //兌換卷 by code 
    public function getRedeemByCode(string $code)
    {
      if($row = $this->redeem->where('code', $code)->first()){
          return $row;
      }else{
          return false;
      }
    } 
    
    //兌換卷清單 
    public function redeemList(int $page , int $status = 0)
    {
      $model = $this->redeem->where('status', $status)
                          ->offset(Redeem::PAGE_PER * $page)
                          ->limit(Redeem::PAGE_PER);
      return $model->get();
    } 

    //兌換卷清單 
    public function redeemVideo(int $memberId , int $videoId , string $cdoe)
    {
      
    } 
    
    //代碼是否存在 或 己過期 
    public function redeemCode(string $code) 
    {
      return $this->redeem->where('code',$code)->where('end',">=",date("Y-m-d H:i:s"))->exists();
    }
}
