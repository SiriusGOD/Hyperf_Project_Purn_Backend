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

use PHPUnit\Framework\TestCase;
use Hyperf\Testing\Client;
use HyperfTest\HttpTestCase;
use App\Service\ActorService;
use App\Service\VideoService;
use App\Util\URand;
/**
 * @internal
 * @coversNothing
 */
class ActorServiceTest extends HttpTestCase
{
     /**
     * @var Client
     */
    protected $client;

    public function __construct($name = null, array $data = [], $dataName = '' )
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
        $this->actorService = \Hyperf\Utils\ApplicationContext::getContainer()->get(ActorService::class);
        $this->videoService = \Hyperf\Utils\ApplicationContext::getContainer()->get(VideoService::class);
    }

    //影片演員關聯 
    public function testActorVideoCorrespondActor()
    {
        $videoRes = $this->videoService->getVideos([],1);
        $r = rand(0,9);
        $d['tags'] = $videoRes[$r]->actors;
        $ActorCorresponds = $this->actorService->videoCorrespondActor($d, $videoRes[$r]->id);
        $videoActor = $this->videoService->getVideosByCorresponds($ActorCorresponds ,1);
        $actors = $videoActor[0]->actors ;
        $flag=false;  
        foreach(explode("," , $d['tags']) as $v1){
          foreach(explode("," ,$actors) as $v2){
            if($v1==$v2){
              $flag=true;  
            }   
          }
        }
        $this->assertSame($flag, true);
    }

    //新增ACTOR
    public function testActorStore()
    {
        $urand = new URand();
        $data['id'] = null;
        $data['user_id']=1;
        $data['name'] = $urand->getRandActor();
        $data['sex']=1;
        $res1 = $this->actorService->storeActor($data);
        $res2 = $this->actorService->findActor((string)$data['name']);
        $this->assertSame($data['name'], $res2->name);
    }
}
