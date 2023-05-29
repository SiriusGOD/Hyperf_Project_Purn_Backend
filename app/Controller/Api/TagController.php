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
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Service\TagGroupService;
use App\Service\TagService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;
use Carbon\Carbon;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class TagController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, TagService $service)
    {
        // $data = $service->getTags();
        $data = $service->getIsInitTags();
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
        $group_id = (int) $request->input('group_id');
        $data = $service->searchGroupTags($group_id);
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'getTagDetail')]
    public function getTagDetail(RequestInterface $request, TagService $service)
    {
        $tag_id = (int) $request->input('tag_id');
        $data = $service->getTagDetail($tag_id);
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'search')]
    public function search(RequestInterface $request, TagService $service)
    {
        $ids = $request->input('tag_ids', [0]);
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', Constants::DEFAULT_PAGE_PER);
        $filter = Constants::FEED_TYPES[$request->input('filter')] ?? null;
        $result = $service->searchByTagIds([
            'ids' => $ids,
            'page' => $page,
            'limit' => $limit,
            'sort_by' => $request->input('sort_by'),
            'is_asc' => $request->input('is_asc'),
            'filter' => $filter,
        ]);

        $data = [
            'models' => $result,
        ];

        $path = '/api/tag/search';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }
}
