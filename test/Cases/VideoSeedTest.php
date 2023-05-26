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
namespace HyperfTest\Cases;

use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\VideoService;
use App\Model\ImportVideo;
use App\Service\TagService;
use App\Service\ActorService;
/**
 * @internal
 * @coversNothing
 */
class VideoSeedTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
    protected $videoService;
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->videoService = make(VideoService::class);
    }

    //寫入DB
    public function insertData($model)
    {
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
    }

    // Video計算任務-       
    public function testDatatVideo()
    {
        $limit = 10;
        $count =ImportVideo::count();
        $totalIterations = ceil($count/$limit);
        for ($i = 0; $i < $totalIterations; $i++) {
            $models = ImportVideo::where('is_calc', 0)->orderBy('id', 'desc')->limit($limit)->get();
            if (count($models) > 0) {
                foreach ($models as $model) {
                    self::insertData($model);
                    $model->is_calc =1;
                    $model->save();
                }
                errLog('Video计算任务'.$i.'次');
                usleep(200);
            }
        }
        errLog('Video计算任务-end');
    }

}
