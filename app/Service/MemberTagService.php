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

use App\Model\MemberTag;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

class MemberTagService
{
    public const CACHE_KEY = 'member:tag:';
    protected Redis $redis;
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(Redis $redis, LoggerFactory $loggerFactory)
    {
        $this->redis = $redis;
        $this->logger = $loggerFactory->get('reply');
    }
    
    //member tag推廌用
    public function addMemberTag(array $data)
    {
        if (MemberTag::where('member_id',$data['member_id'])->where('tag_id', $data['tag_id'])->exists()) {
            $model = MemberTag::where('member_id', $data['member_id'])->where('tag_id', $data['tag_id'])->first();
            $model->count = $model->count +1; 
        } else {
            $wg = new \Hyperf\Utils\WaitGroup();
            $wg->add(1);
            $model = new MemberTag();
            co(function() use ($model, $wg, $data){
              $model->member_id = $data['member_id'];
              $model->tag_id = $data['tag_id'];
              $model->count = 1;
              $model->save();
              $wg->done();             
            });
            $wg->wait();
        }
        if($model->save()){
          return true;
        }else{
          return false;
        }
    }

}
