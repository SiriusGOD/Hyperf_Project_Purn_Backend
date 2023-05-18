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
namespace App\Task;

use App\Model\Channel;
use App\Service\ChannelService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;

#[Crontab(name: 'ChannelTask', rule: '* * * * *', callback: 'execute', memo: '渠道註冊計算任務')]
class ChannelTask
{
    protected $channelService;
    private \Psr\Log\LoggerInterface $logger;
    
    public function __construct(ChannelService $channelService, LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->channelService = $channelService;
    }

    public function execute()
    {
        $models = Channel::all();
        if (count($models) == 0) {
            return;
        }
        $this->logger->info('渠道註冊計算任務-開始'.date("YmdH"));
        foreach ($models as $model) {
            $parsedUrl = parse_url($model->url);
            $domain = isset($parsedUrl['host'])?$parsedUrl['host']:"";
            if(!empty($domain)){
                $this->channelService->calcChannelCount2DB($domain ,$model->id,"member");
                $this->channelService->calcChannelCount2DB($domain ,$model->id,"member");
            }else{
                errLog("DB channel url error!!");
            }
        }
        $this->logger->info('渠道註冊計算任務-開始'.date("YmdH"));
    }

}
