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
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\MemberService;
/**
 * @internal
 * @coversNothing
 */
class StageVideoApiTest extends HttpTestCase
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
    //新增
    public function testStore()
    {
        $user = Member::first();
        $token = auth()->login($user);
        make(MemberService::class)->saveToken($user->id, $token);
        $paradata = [ "name" => "test1" ];
        $data = $this->client->post(
          '/api/stage_video/createStageCate',
          $paradata, 
          [
              'Authorization' => 'Bearer ' . $token,
          ]
        );
        $this->assertSame(200, (int)$data['code']);
   }
    //edit
   public function testStageEdit()
   {
       $user = Member::first();
       $token = auth()->login($user);
       make(MemberService::class)->saveToken($user->id, $token);
       $para = [ "name" => "test999" , 'id'=>3 ];
       $data = $this->client->post(
         '/api/stage_video/editStageCate',
         $para, 
         [
             'Authorization' => 'Bearer ' . $token,
         ]
       );
       $this->assertSame(200, (int)$data['code']);
   }

    //刪除stage video cate 
   public function testDelStageEdit()
   {
       $user = Member::first();
       $token = auth()->login($user);
       make(MemberService::class)->saveToken($user->id, $token);
       $para = [ 'id'=>7 ];
       $data = $this->client->put(
         '/api/stage_video/deleteStageCate',
         $para, 
         [
             'Authorization' => 'Bearer ' . $token,
         ]
       );
       $this->assertSame(200, (int)$data['code']);
   }

    //清單stage video cate 
   public function testStageCateList()
   {
       $user = Member::first();
       $token = auth()->login($user);
       make(MemberService::class)->saveToken($user->id, $token);
       $para = ['page'=>0];
       $data = $this->client->get(
         '/api/stage_video/stageCateList',
         $para, 
         [
             'Authorization' => 'Bearer ' . $token,
         ]
       );
       $this->assertSame($user->id, (int)$data['data']['models'][0]["member_id"]);
   }

    //stage video 
  public function testStageVideoDefault()
   {
       $user = Member::first();
       $token = auth()->login($user);
       make(MemberService::class)->saveToken($user->id, $token);
       $para = ['video_id'=>2];
       $data = $this->client->post(
         '/api/stage_video/stageVideoDefault',
         $para, 
         [
             'Authorization' => 'Bearer ' . $token,
         ]
       );
       $this->assertSame(200, (int)$data['code']);
   }
    //stage video 
   public function testStageVideo()
   {
       $user = Member::first();
       $token = auth()->login($user);
       make(MemberService::class)->saveToken($user->id, $token);
       $para = ['video_id'=>4 , "cate_id"=>8];
       $data = $this->client->post(
         '/api/stage_video/stageVideo',
         $para, 
         [
             'Authorization' => 'Bearer ' . $token,
         ]
       );
       $this->assertSame(200, (int)$data['code']);
   }
}
