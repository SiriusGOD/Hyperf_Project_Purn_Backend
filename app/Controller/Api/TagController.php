<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Request\TagRequest;
use App\Service\TagService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * @Controller()
 */
class TagController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(RequestInterface $request, TagService $service)
    {
        $data = $service->getTags();
        return $this->success($data->toArray());
    }

    /**
     * @RequestMapping(path="create", methods="post")
     */
    public function create(TagRequest $request, TagService $service)
    {
        $userId = auth('jwt')->user()->getId();
        $service->createTag($request->input('name'), $userId);

        return $this->success();
    }
}
