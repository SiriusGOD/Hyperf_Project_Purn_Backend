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

use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use App\Model\ImportVideo;
use App\Service\TagService;
use App\Service\ActorService;
use App\Service\VideoService;

#[Crontab(name: 'VideoTask', rule: '* * * * *', callback: 'execute', memo: '影片定時任務')]
class VideoTask
{
    protected Redis $redis;

    protected $service;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct( LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
    }

    public function execute()
    {
        $limit = 10;
        $count = ImportVideo::count();
        $totalIterations = ceil($count/$limit);
        for ($i = 0; $i < $totalIterations; $i++) {
            $models = ImportVideo::where('is_calc', 0)->orderBy('id', 'desc')->limit($limit)->get();
            if (count($models) > 0) {
                foreach ($models as $model) {
                    if($model->created_at != NULL){
                      self::insertData($model);
                      $model->is_calc =1;
                      $model->save();
                    }
                }
                $this->logger->info('有商品過期');
                usleep(200);
            }
        }
        $this->logger->info('有商品過期');
    }
  
    //寫入DB
    public function insertData($model)
    {
      try{
        $wg = new \Hyperf\Utils\WaitGroup();
        $wg->add(1);
        $data= $model->toArray();
        $service = make(VideoService::class);
        $tagService = make(TagService::class);
        $actorService = make(ActorService::class);
        co(function () use ($wg, $data, $service,$tagService, $actorService) {
          unset($data['is_calc']);
          unset($data['id']);
          $video = $service->storeVideo($data);
          $tagService->videoCorrespondTag($data, $video->id);
          $actorService->videoCorrespondActor($data, $video->id);
          $wg->done();
          usleep(100);
        });
        $wg->wait();
      }catch(\Exception $e){
          print_r([$e->getMessage()]);
      }
    }
}
