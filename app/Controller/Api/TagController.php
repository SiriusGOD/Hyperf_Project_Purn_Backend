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

#[Controller]
class TagController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(RequestInterface $request, TagService $service)
    {
        $data = $service->getTags();
        return $this->success($data->toArray());
    }

    #[RequestMapping(methods: ['GET'], path: 'popular')]
    public function popular(RequestInterface $request, TagService $service)
    {
        $data = $service->getPopularTag();
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'groupList')]
    public function groupList(RequestInterface $request, TagGroupService $service)
    {
        $data = $service->getTags();
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'searchGroupTags')]
    public function searchGroupTags(RequestInterface $request, TagGroupService $service)
    {
        $group_id = $request->input('group_id');
        $data = $service->searchGroupTags($group_id);
        return $this->success($data);
    }
}
