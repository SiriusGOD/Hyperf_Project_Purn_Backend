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
use App\Service\TagGroupService;
use App\Service\TagService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
class TagController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, TagService $service)
    {
        $data = $service->getTags();
        return $this->success(['models' => $data->toArray()]);
    }

    #[RequestMapping(methods: ['POST'], path: 'popular')]
    public function popular(RequestInterface $request, TagService $service)
    {
        $data = $service->getPopularTag();
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'groupList')]
    public function groupList(RequestInterface $request, TagGroupService $service)
    {
        $data = $service->getTags();
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'searchGroupTags')]
    public function searchGroupTags(RequestInterface $request, TagGroupService $service)
    {
        $group_id = $request->input('group_id');
        $data = $service->searchGroupTags($group_id);
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'getTagDetail')]
    public function getTagDetail(RequestInterface $request, TagService $service)
    {
        $tag_id = $request->input('tag_id');
        $data = $service->getTagDetail($tag_id);
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'test')]
    public function test(RequestInterface $request, TagService $service)
    {
        $data = $service->calculateTop6Tag();
    }
}
