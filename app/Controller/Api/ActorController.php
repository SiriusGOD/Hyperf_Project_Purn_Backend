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
use App\Service\ActorService;
use App\Service\ActorClassificationService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class ActorController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(RequestInterface $request, ActorService $service)
    {
        $page = (int) $request->input('page', 0);
        $data['models'] = $service->getActors($page);
        $data['page'] = $page;
        $data['step'] = Constants::DEFAULT_PAGE_PER;
        $path = '/api/actor/list';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'count')]
    public function count(ActorService $service)
    {
        $result = $service->getActorCount();
        return $this->success(['count' => $result]);
    }

    #[RequestMapping(methods: ['GET'], path: 'getClassification')]
    public function getClassification(ActorClassificationService $service)
    {
        $result = $service->getClassification();
        return $this->success(['models' => $result]);
    }

    #[RequestMapping(methods: ['GET'], path: 'getListByClassification')]
    public function getListByClassification(RequestInterface $request, ActorClassificationService $service)
    {
        $type_id = (int)$request->input('type_id', 0);
        $result = $service->getListByClassification($type_id);
        return $this->success(['models' => $result]);
    }
}
