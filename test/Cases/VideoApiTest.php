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
    public function testApiSearch()
    {
        $rand = new URand();
        $title = $rand->getRandTitle();
        $res2 = $this->client->get('/api/video/search',['title'=>$title]);
        $this->assertSame(200, (int) $res2['code']);
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
