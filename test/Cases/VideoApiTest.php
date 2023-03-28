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
use App\Service\UserService;
use App\Service\VideoService;
use App\Service\TagService;
use App\Util\URand;
use App\Model\User;
/**
 * @internal
 * @coversNothing
 */
class VideoApiTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;
  
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->videoService = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
    }

    //vidoe list api 測試
    public function testApiList()
    {
        $res1 = $this->client->get('/api/video/list');
        $this->assertSame(200, (int) $res1['code']);
        $res2 = $this->client->get('/api/video/list',['page'=>2]);
        $this->assertSame(200, (int) $res2['code']);
        $this->assertNotSame($res2['data']["models"][0]["id"], $res1['data']["models"][0]["id"]);
    }

    //vidoe search api 測試
    public function testApiData()
    {
        $rand = new URand();
        $rating = rand(20000,200000);
        $like = floor($rating/15);
        $data = [
            'type'             => 2,
            'fan_id'           => 1,
            'p_id'             => 1,
            'user_id'          => 1,
            'music_id'         => 1,
            'description'      => "test",
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
            'category'         => 1,
            'via'              => 'live',
            'onshelf_tm'       => time(),
            'rating'           => '12',
            'refresh_at'       => time(),
            'created_at'       => date("Y-m-d H:i:s"),
            'is_free'          => 1,
            'like'             => $like,
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
        $data['tags'] = $rand->getRandTagActor(7,"TAG");
        $data['actors'] = $rand->getRandTagActor(8,"ACTOR");
        $data['title'] = $rand->getRandTitle();
        $vcount1 = $this->videoService->videoCount(); 
        $res = $this->client->post('/api/video/data', $data);
        $vcount2 = $this->videoService->videoCount(); 
        $this->assertSame($vcount1+1  , $vcount2);
        $this->assertSame(200, (int) $res['code']);
    }
    //vidoe search api 測試
    public function testApiSearch()
    {
        $res = $this->videoService->getVideos([],0);
        $res2 = $this->client->get('/api/video/search',['title'=>$res[0]->title]);
        $this->assertSame($res[0]->title, $res2['data']["models"][0]["title"]);
    }

    //vidoe list api 有tag測試
    public function testApiListHasTags()
    {
        $tagService = \Hyperf\Utils\ApplicationContext::getContainer()->get(TagService::class);
        $tags = $tagService->getTags();
        $data = array_slice( $tags->toArray(),0,3);
        $ids  = array_column($data , 'id') ;
        $names  = array_column($data , 'name') ;
        $res1 = $this->client->get('/api/video/list',[ 'tags' => $ids ]);
        $assertCount = 0;
        for($i=1; $i<=4; $i++){
          $tagstr = $res1['data']["models"][$i]['tags'];
          $flag = false;
          foreach($names as $search_string){
            if (strpos($tagstr, $search_string) !== false && $flag==false) {
                $assertCount ++;
                $flag = true;
            } 
          }
        }
        $this->assertSame(4, $assertCount );
    }

    //推廌影片 --ERROR 沒結果...  
    public function testVideoSuggest()
    {
        $user = User::find(2);
        $token = auth()->login($user);
        make(UserService::class)->saveToken($user->id, $token);
        $data = $this->client->get('/api/video/suggest', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertSame(200, (int) $data['code']);
    }

}
