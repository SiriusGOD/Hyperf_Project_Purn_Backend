<?php

declare(strict_types=1);

use App\Util\URand;
use App\Service\VideoService;
use App\Service\TagService;
use App\Service\ActorService;
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class VideoSeed implements BaseInterface
{
    public function up(): void
    {
        $rand = new URand();
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
            'cover_full'       => '/upload/xiao/20230425/2023042516462562832.jpg'
        ];

        for($i=1 ; $i<=36 ; $i++){
            $insertData['tags'] = $rand->getRandTagActor(5,"TAG");
            $insertData['actors'] = $rand->getRandTagActor(5,"ACTOR");
            $insertData['title'] = $rand->getRandTitle();
            $insertData['is_free'] = $i%3;

            $video = make(VideoService::class)->storeVideo($insertData);
            if($insertData['tags']){
                $exps = explode(",",$insertData['tags']);
                foreach($exps as $str){
                    $tag = make(TagService::class)->createTagByName($str, 1);
                    make(TagService::class)->createTagRelationship(\App\Model\Video::class,$video->id ,$tag->id );
                } 
            }
            if($insertData['actors']){
                $exps = explode(",",$insertData['tags']);
                foreach($exps as $str){
                  $data["id"] = null; 
                  $data["name"] = $str; 
                  $data["user_id"] = 1; 
                  $data["sex"] = 1; 
                  $actor = make(ActorService::class)->storeActor($data); 
                  make(ActorService::class)->createActorRelationship("video",$video->id ,$actor->id );
                }
            }
        }
    }

    public function down(): void
    {
        \App\Model\Video::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
