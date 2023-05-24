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
    //vidoe csv匯入 
    public function ansertCsvdata()
    {
        $handle = fopen(BASE_PATH . '/storage/import/videos.csv', 'r');
        $key = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
          $keys = array(
            'id',
            'type',
            'category',
            'via',
            'mod',
            '_id',
            'title',
            'source',
            'cover_thumb',
            'cover_full',
            'directors',
            'actors',
            'tags',
            'release_time',
            'duration',
            'total_click',
            'views',
            'likes',
            'is_free',
            'coins',
            'sales',
            'comment',
            'uuid',
            'created_at',
            'updated_at',
            'backtag',
            'tags2'
        );

        $result = array_combine($keys, $data);
        $result['tags2'] = str_replace('.', ',', $result['tags2']);
        $result['tags'] = $result['tags2'];
        // 添加新的字段
        $result['music_id'] = 1;
        $result['refreshed_at'] = date("Y-m-d H:i:s");
        $result['full_m3u8'] = '';
        $result['v_ext'] = 'm3u8';
        $result['thumb_width'] = 0;
        $result['thumb_height'] = 0;
        $result['gif_thumb'] = 'cover_full';
        $result['gif_width'] = 0;
        $result['gif_height'] = 0;
        $result['description'] = 'test';
        $result['onshelf_tm'] = time();
        $result['rating'] = '12';
        $result['refresh_at'] = time();
        $result['likes'] = 0;
        $result['thumb_start_time'] = 0;
        $result['thumb_duration'] = 0;
        $result['is_hide'] = 0;
        $result['is_recommend'] = 0;
        $result['is_feature'] = 0;
        $result['is_top'] = 0;
        $result['count_pay'] = 0;
        $result['club_id'] = 0;
        $result['user_id'] = 1;
        unset($result['uuid'],$result['id'],$result['tags2'], $result['comments'], $result['sales'], $result['views'], $result['backtag']);
        //$video = make(VideoService::class)->storeVideo($result);
        $model = new ImportVideo();
        foreach($result as $k =>$v){
          if($k == 'release_time'){
            $model->$k = !empty($v) ? $v:date('Y-m-d H:i:s');
          }elseif($k == 'mod'){
            $model->$k = !empty($v) ? $v:1;
          }elseif($k == 'cover_full'){
            $model->$k = !empty($v) ? $v:1;
          }elseif($k == '_id'){
            $model->$k = !empty($v) ? $v:1;
          }elseif($k == 'user_id'){
            $model->$k = !empty($v) ? $v:1;
          }elseif($k == 'total_click'){
            $model->$k = !empty($v) ? $v:1;
          }else{
            $model->$k = !empty($v) ? $v:null;
          }
        }
        $model->save(); 
        if($key%5==0){
            usleep(500);
        }
          $key++;  
        }
        fclose($handle);
        $this->assertSame(200, 200);
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
        $limit = 4;
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
