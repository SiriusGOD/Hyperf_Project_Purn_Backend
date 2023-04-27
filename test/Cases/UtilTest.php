<?php

namespace HyperfTest\Cases;

use App\Model\Member;
use App\Service\MemberService;
use HyperfTest\HttpTestCase;
use App\Util\Calc;

class UtilTest extends HttpTestCase
{
    public function testCreate()
    {
        $url="https://imgpublic.ycomesc.live/upload/xiao/20230425/2023042516462562832.jpg";
        $imgAttr = Calc::imgSize($url);
        $this->assertSame(2, count($imgAttr));
    }
}
