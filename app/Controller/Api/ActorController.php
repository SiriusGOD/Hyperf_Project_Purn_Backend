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

use App\Service\ActorService;
use App\Service\ObfuscationService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller
 */
class ActorController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(RequestInterface $request, ActorService $service, ObfuscationService $response)
    {
        $offset = $request->input('offset',0);   
        $limit = $request->input('limit',0);   
        $result = $service->getActors($offset ,$limit);
        return $response->replyData($result);
    }

    /**
     * @RequestMapping(path="count", methods="get")
     */
    public function count(ActorService $service, ObfuscationService $response)
    {
        $result = $service->getActorCount();
        return $response->replyData($result);
    }
}