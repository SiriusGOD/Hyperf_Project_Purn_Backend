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
use App\Service\ActorService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller
 */
class ActorController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(RequestInterface $request, ActorService $service)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 0);
        $result = $service->getActors($offset, $limit);
        return $this->success([
            'result' => $result,
        ]);
    }

    /**
     * @RequestMapping(path="count", methods="get")
     */
    public function count(ActorService $service)
    {
        $result = $service->getActorCount();
        return $this->success([
            'count' => $result,
        ]);
    }
}
