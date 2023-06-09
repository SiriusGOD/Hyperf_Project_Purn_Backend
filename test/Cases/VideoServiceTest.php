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
use App\Service\TagService;
use App\Service\ActorService;
use App\Util\URand;

/**
 * @internal
 * @coversNothing
 */
class VideoServiceTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
  
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
    }
    //測試 del video stage
    public function testDelMyStageVideo()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
        $member_id = 1;
        $res = $service->myStageVideo($member_id, 0);
        $ids = [(int)$res->toArray()[2]["id"]];
        $bool = $service->delStageVideo($ids);
        $this->assertSame(true, $bool);
    }
    //測試video stage
    public function testGetMyStageVideo()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
        $member_id = 1;
        $res = $service->myStageVideo($member_id, 0);
        $this->assertSame($res->toArray()[0]["member_id"], $member_id);
    }
    //測試video stage
    public function testVideoStage()
    {
        $wg = new \Hyperf\Utils\WaitGroup();
        for($i=1 ; $i<=10 ; $i++)
        {
            $wg->add(1);
            co(function () use ($wg) {
              $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
              $res = $service->storeStageVideo(rand(1,100), 1);
              $this->assertSame(true, $res);
              $wg->done();
            });
        }
        $wg->wait();
    }
    //測試find
    public function testVideoFind()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
        $row = $service->getVideos([],0); 
        $res = $service->find((int)$row[0]->id);
        $this->assertSame((int)$row[0]->id, $res->id);
        $this->assertSame($row[0]->title, $res->title);
    }

    //測試Search
    public function testVideoSearch()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
        $data = $service->getVideos([],0); 
        $res = $service->searchVideo($data[0]->title ,null ,0 ,0);
        $this->assertSame($res[0]->title, $data[0]->title);
    }

    //測試Count
    public function testVideoStore()
    {
        $rand = new URand();
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
        $tagService = \Hyperf\Utils\ApplicationContext::getContainer()->get(TagService::class);
        $actorService = \Hyperf\Utils\ApplicationContext::getContainer()->get(ActorService::class);
        $rating = rand(20000,200000);
        $like = floor($rating/15);
        // 插入数据
        $insertData = [
            'type'             => 2,
            'fan_id'           => 1,
            'p_id'             => 1,
            'user_id'          => 1,
            'music_id'         => 1,
            'coins'            => 20,
            'm3u8'             => "/watch8/a77b2b0863aeaab3be89a6f1b85baa82/a77b2b0863aeaab3be89a6f1b85baa82.m3u8",
            'refreshed_at'     => date("Y-m-d H:i:s"),
            'full_m3u8'        => '',
            'v_ext'            => 'm3u8',
            'duration'         => 111,
            'cover_thumb'      => '/new/av/20211220/2021122023012418421.png',//封面
            'thumb_width'      => 0,
            'thumb_height'     => 0,
            'gif_thumb'        => 'cover_full',//封面 竖
            'gif_width'        => 0,
            'gif_height'       => 0,
            'directors'        => 'category',
            'description'      => "test",
            'category'         => 1,
            'via'              => 'live',
            'onshelf_tm'       => time(),
            'rating'           => '12',
            'refresh_at'       => time(),
            'created_at'       => date("Y-m-d H:i:s"),
            'is_free'          => 1,
            'likes'             => $like,
            'comment'          => 0,
            'status'           => 1,
            'thumb_start_time' => 0,
            'thumb_duration'   => 0,
            'is_hide'          => 0,
            'is_recommend'     => 0,
            'is_feature'       => 0,
            'is_top'           => 0,
            'count_pay'        => 0,
            'club_id'          => 0,
        ];

        for($i=1 ; $i<=20 ; $i++){
            $insertData['tags'] = $rand->getRandTagActor(5,"TAG");
            $insertData['actors'] = $rand->getRandTagActor(5,"ACTOR");
            $insertData['title'] = $rand->getRandTitle();
            $video = $service->storeVideo($insertData);
            if($insertData['tags']){
                $exps = explode(",",$insertData['tags']);
                foreach($exps as $str){
                    $tag = $tagService->createTagByName($str, 1);
                    $tagService->createTagRelationship("video",$video->id ,$tag->id );
                } 
            }
            if($insertData['actors']){
                $exps = explode(",",$insertData['tags']);
                foreach($exps as $str){
                  $data["id"] = null; 
                  $data["name"] = $str; 
                  $data["user_id"] = 1; 
                  $data["sex"] = 1; 
                  $actor = $actorService->storeActor($data); 
                  $actorService->createActorRelationship("video",$video->id ,$actor->id );
                }
            }
        }
        $this->assertSame(2, (int)$video->type);
    }

}
