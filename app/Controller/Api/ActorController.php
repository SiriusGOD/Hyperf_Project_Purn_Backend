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
//use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller
 */
class ActorController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(ActorService $service, ObfuscationService $response)
    {
        $result = $service->getActors();
        return $response->replyData($result);
    }
}

