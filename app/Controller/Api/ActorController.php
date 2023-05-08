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

use App\Constants\Constants;
use App\Controller\AbstractController;
use App\Service\ActorClassificationService;
use App\Service\ActorService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Model\MemberFollow;
use App\Model\MemberTag;

#[Controller]
class ActorController extends AbstractController
{
    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, ActorService $service)
    {
        $userId = auth('jwt')->user()->getId();
        $page = (int) $request->input('page', 0);
        $data = [];
        $data['models'] = $service->getActors($page, $userId);
        $path = '/api/actor/list';
        $simplePaginator = new SimplePaginator($page, Constants::DEFAULT_PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'count')]
    public function count(ActorService $service)
    {
        $result = $service->getActorCount();
        return $this->success(['count' => $result]);
    }

    #[RequestMapping(methods: ['POST'], path: 'getClassification')]
    public function getClassification(ActorClassificationService $service)
    {
        $result = $service->getClassification();
        return $this->success(['models' => $result]);
    }

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'getListByClassification')]
    public function getListByClassification(RequestInterface $request, ActorClassificationService $service)
    {
        $userId = auth('jwt')->user()->getId();
        $type_id = (int) $request->input('type_id', 0);
        $result = $service->getListByClassification($type_id, $userId);
        return $this->success(['models' => $result]);
    }

    #[Middleware(ApiAuthMiddleware::class)]
    #[RequestMapping(methods: ['POST'], path: 'getActorDetail')]
    public function getActorDetail(RequestInterface $request, ActorService $service)
    {
        $userId = auth('jwt')->user()->getId();
        $actor_id = (int) $request->input('actor_id', 0);
        $result = $service->getActorDetail($actor_id, $userId);
        return $this->success(['models' => $result]);
    }

    #[RequestMapping(methods: ['POST'], path: 'isFollow')]
    public function isFollow(RequestInterface $request, ActorService $service)
    {
        $memberId = auth()->user()->getId();
        $id = $request->input('actor_id', 0);
        $exist = $service->isFollow($memberId, $id);

        return $this->success([
            'is_follow' => $exist,
        ]);
    }
}
