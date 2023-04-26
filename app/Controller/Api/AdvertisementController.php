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
namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Service\AdvertisementService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class AdvertisementController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, AdvertisementService $service)
    {
        $data = $service->getAdvertisements();
        $result = [];
        // 取得網址前綴
        $url = $request->url();
        $urlArr = parse_url($url);
        $port = $urlArr['port'] ?? '80';
        if(!empty(env("TEST_IMG_URL"))){
            $host = env("TEST_IMG_URL");
        }else{
            $host = $urlArr['scheme'] . '://' . $urlArr['host'] . ':' . $port;
        }
        foreach ($data as $item) {
            $item['image_url'] = $host . $item['image_url'];
            $result[] = $item;
        }

        return $this->success($result);
    }
}
