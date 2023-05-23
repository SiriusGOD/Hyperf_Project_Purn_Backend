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

use App\Model\ImportVideo;
use Hyperf\Crontab\Annotation\Crontab;
use App\Service\VideoService;
use Hyperf\Logger\LoggerFactory;

#[Crontab(name: 'VideoTask', rule: '* * * * *', callback: 'execute', memo: '影片計算任務')]
class VideoTask
{
    private \Psr\Log\LoggerInterface $logger;

    public function __construct( LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
    }

    public function execute()
    {
        $this->logger->info('Video計算任務-Start');
        // Video計算任務-Start       
        $models = ImportVideo::where('is_calc',0)->orderBy('id','desc')->limit(6)->get();
        if (count($models->toArray()) > 0) {
        foreach ($models as $model) {
            $data=$model->toArray();
            unset($data['is_calc']);
            make(VideoService::class)->storeVideo($data);
            $model->is_calc =1;
            $model->save();
          }
        }
        $this->logger->info('Video計算任務-Start');
    }

}
