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

use App\Model\UserTag;
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
    //測試user suggest TAG 假資料 
    public function testSuggestByUser()
    {
        $expect = 1;
        $userTag = new UserTag();
        $userTag->tag_id = $expect;
        $userTag->user_id = 0;
        $userTag->count = 1;
        $userTag->save();

        $service = make(SuggestService::class);
        $result = $service->getTagProportionByUser(0);

        $userTag->delete();
        $this->assertSame($expect, $result[0]['tag_id']);
    }
}
