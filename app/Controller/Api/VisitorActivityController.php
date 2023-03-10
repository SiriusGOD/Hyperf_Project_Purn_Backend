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

use App\Service\ObfuscationService;
use App\Service\BaseService;
use App\Service\VisitorActivityService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\Redis;

/**
 * @Controller
 */
class VisitorActivityController
{
    protected Redis $redis;

    /**
     * @RequestMapping(path="visit", methods="get")
     */
    public function visit(RequestInterface $request, VisitorActivityService $service, ObfuscationService $response , BaseService $baseService)
    {
        $siteId = (int) $request->input('site_id', 1);
        $headers = $request->getHeaders();
        $params = $request->getServerParams();

        $ip = $baseService->getIp($headers, $params);

        $isCache = $service->isCache($ip, $siteId);

        if (empty($ip) or $isCache) {
            return $response->replyData(['status' => true]);
        }

        $service->storeVisitorActivity($ip, $siteId);

        return $response->replyData(['status' => true]);
    }
}
