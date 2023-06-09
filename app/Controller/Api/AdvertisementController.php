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
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Service\AdvertisementService;
use App\Util\General;
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
        $host = \Hyperf\Support\env('IMAGE_GROUP_ENCRYPT_URL', 'https://new.cnzuqiu.mobi');
        foreach ($data as $item) {
            $item['image_url'] = $host . $item['image_url'];
            $result[] = $item;
        }

        return $this->success($result);
    }
}
