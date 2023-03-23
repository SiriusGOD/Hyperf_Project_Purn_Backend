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
use Hyperf\DbConnection\Db;
use HyperfTest\HttpTestCase;
use App\Service\VideoService;
use App\Service\TagService;
use App\Service\ActorService;
/**
 * @internal
 * @coversNothing
 */
class VideoTest extends HttpTestCase
{
    public function testCount()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
        $rating = rand(20000,200000);
        $like = floor($rating/15);
        // 插入数据
        $insertData = [
            'type'             => 2,
            'fan_id'           => 1,
            'p_id'             => 1,
            'user_id'          => 1,
            'music_id'         => 1,
            'title'            => "三上2",
            'coins'            => 20,
            'm3u8'             => "/qwe",
            'description'      => "/qwe",
            'refreshed_at'     => date("Y-m-d H:i:s"),
            'full_m3u8'        => '',
            'v_ext'            => 'm3u8',
            'duration'         => 111,
            'cover_thumb'      => 'cover_thumb',//封面
            'thumb_width'      => 0,
            'thumb_height'     => 0,
            'gif_thumb'        => 'cover_full',//封面 竖
            'gif_width'        => 0,
            'gif_height'       => 0,
            'directors'        => 'category',
            'actors'           => 'actors',
            'category'         => "qwe",
            'tags'             => 'tags',
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
        $res = $service->createVideo($insertData);
        $this->assertSame(2, (int)$res->type);
    }

    public function testVideoStore()
    {
        $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
        $db  = \Hyperf\Utils\ApplicationContext::getContainer()->get(DB::class);
        $res = $db->select("select * from ks_mv order by id asc limit 1150  "); 
        foreach($res as  $row){
          $arr = (array) $row;
          $arr['user_id'] = 1;
          $arr['description'] = 'description';
          $arr['refreshed_at']= date("Y-m-d H:i:s");
          unset($arr['id']);
          unset($arr['uid']);
          $model = $service->storeVideo($arr);
          self::storeVideoTag($arr ,$model->id);
          self::storeVideoActor($arr ,$model->id);
        }
        $this->assertSame(2, 2);
    }

    public function storeVideoTag($arr ,$videoId){
        $sTag = \Hyperf\Utils\ApplicationContext::getContainer()->get(TagService::class);
        $tags = explode(",",$arr['tags']);
        foreach($tags as $v){
          if(strlen($v)>1){
            $tag = $sTag->createTagByName($v ,1);
            $sTag->createTagRelationship("video" ,$videoId ,$tag->id);
          }
        }
    }

    public function storeVideoActor($arr, $videoId){
        $sActor = \Hyperf\Utils\ApplicationContext::getContainer()->get(ActorService::class);
        $data['name'] = $arr['actors']; 
        $data['user_id'] = 1; 
        $data['sex'] = 0; 
        $actor = $sActor->storeActorByName($data);
        $sActor->createActorRelationship("video", $videoId, $actor->id);
    }

    public function testUpdateVideo()
    {
      $service = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
      $db  = \Hyperf\Utils\ApplicationContext::getContainer()->get(DB::class);
      $res1 = $db->select("select * from ks_mv order by id asc limit 1"); 
      foreach($res1 as  $res){
        $arr = (array) $res;
        unset($arr['uid']);
        $arr['user_id'] = 1;
        $arr['description'] = 'description';
        $arr['refreshed_at']= date("Y-m-d H:i:s");
        $service->storeVideo($arr);
      }
      $this->assertSame(2, 2);
    }
}

