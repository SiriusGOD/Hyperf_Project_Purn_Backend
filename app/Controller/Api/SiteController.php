<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\ObfuscationService;
use App\Service\SiteService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller()
 */
class SiteController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(RequestInterface $request, SiteService $service, ObfuscationService $response)
    {
        $data = $service->getSites();

        return $response->replyData($data);
    }
}
