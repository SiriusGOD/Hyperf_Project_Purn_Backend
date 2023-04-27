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

use App\Model\Member;
use App\Service\MemberService;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\VideoService;
use App\Service\TagService;
use App\Util\URand;
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
    protected $videoService;
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->videoService = make(VideoService::class);
    }

    //vidoe list api 測試
    public function testApiList()
    {
        $res1 = $this->client->post('/api/video/list');
        $this->assertSame(200, (int) $res1['code']);
        $res2 = $this->client->post('/api/video/list',['page'=>2]);
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
            'duration'         => rand(0,999),
            'cover_thumb'      => '/new/av/20211220/2021122023012418421.png',//封面
            'thumb_width'      => 0,
            'thumb_height'     => 0,
            'gif_thumb'        => '/new/av/20211220/2021122023012418421.png',
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
        $data['tags'] = $rand->getRandTagActor(7,"TAG");
        $data['actors'] = $rand->getRandTagActor(8,"ACTOR");
        $data['title'] = $rand->getRandTitle();
        $vcount1 = $this->videoService->videoCount(); 
        $str= '{"title":"11\u8105\u8feb\u96c6\u56e3\u75f4\u6f22\u5e2b\u30ec\u25cb\u30d7 \u751f\u811a\u592a\u3082\u3082\u3092\u64ab\u3067\u56de\u3059\u6307\u5148\u2026\u5c3b\u306b\u64e6\u308a\u4ed8\u3051\u3089\u308c\u308b\u30c1\u25cb\u30dd\u2026\u6050\u6016\u3067\u811a\u3092\u30ac\u30af\u30ac\u30af\u3055\u305b\u62b5\u6297","_id":"15428_ymd","type":"1","mod":"1","category":"\u4e03\u5ea6","category_id":"143","p_id":"3510356","duration":"7215","source":"\/videos2\/600102281a4ba8ae332ea5603b510611\/600102281a4ba8ae332ea5603b510611.m3u8","cover_thumb":"\/upload\/xiao\/20230425\/2023042511210171330.jpg","cover_full":"\/upload\/xiao\/20230425\/2023042511210190648.jpg","actors":"","tags":"\u5de8\u4e73,\u7f8e\u817f","directors":"","coins":"0","uuid":"","release_at":"","created_at":"1682394073","sign":"f5502b83562c4e4d7856f4bf835eaec3"}';
        $data = json_decode($str,true);
        $res = $this->client->post('/api/video/data', $data);
        $vcount2 = $this->videoService->videoCount(); 
        $this->assertSame($vcount1+1  , $vcount2);
        $this->assertSame(200, (int)$res['code']);
    }
    //vidoe search api 測試
    public function testApiSearch()
    {
        $res = $this->videoService->getVideos([],0);
        $res2 = $this->client->post('/api/video/search',['title'=>$res[0]->title]);
        $this->assertSame($res[0]->title, $res2['data']["models"][0]["title"]);
    }

    //vidoe stage api
    public function testApiStageList()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $data1 = $this->client->post('/api/video/stagelist', [
          "page" => 0
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $data2 = $this->client->post('/api/video/stagelist', [
          "page" => 1
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        
        $this->assertNotSame($data1['data']["models"][0]["id"], $data2['data']["models"][0]["id"]  );
    }


    //vidoe find api
    public function testApiFind()
    {
        $tagService = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
        $row = $tagService->getVideos([],0); 
        $res1 = $this->client->post('/api/video/find',[ 'id' => (int)$row[0]->id ]);
        $this->assertSame((int)$row[0]->id , $res1['data']["models"]['id'] );
    }
  
    //vidoe list api 有tag測試
    public function testApiListHasTags()
    {
        $tagService = \Hyperf\Utils\ApplicationContext::getContainer()->get(TagService::class);
        $tags = $tagService->getTags();
        $data = array_slice( $tags->toArray(),0,3);
        $ids  = array_column($data , 'id') ;
        $names  = array_column($data , 'name') ;
        $res1 = $this->client->post('/api/video/list',[ 'tags' => $ids ]);
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
    //public function testVideoSuggest()
    //{
    //}

}
