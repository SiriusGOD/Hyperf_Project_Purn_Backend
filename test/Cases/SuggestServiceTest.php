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
use App\Service\SuggestService;
use App\Service\TagService;
use App\Util\URand;

/**
 * @internal
 * @coversNothing
 */
class SuggestServiceTest extends HttpTestCase
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

    //測試user suggest TAG 假資料 
    public function testVideoSuggestByUser()
    {
        $userId = 2;
        $rand = new URand();
        $tagService = \Hyperf\Utils\ApplicationContext::getContainer()->get(TagService::class);
        $suggestService = \Hyperf\Utils\ApplicationContext::getContainer()->get(SuggestService::class);
        $tags = $tagService->getTags();
        $data = array_slice( $tags->toArray(),0, count($tags->toArray()) );
        $ids  = array_column($data , 'id') ;
        $randKeys = $rand->getRandTag($ids, 20);
        foreach($randKeys as $key){
            $tagId = $key;
            
            $model = $suggestService->storeUserTag($tagId,$userId);
        } 
        $this->assertSame($userId, $model->user_id );
        $this->assertSame($tagId, $model->tag_id );
    }
}
