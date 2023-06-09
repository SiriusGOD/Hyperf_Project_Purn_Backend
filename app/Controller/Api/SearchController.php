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
use App\Request\ImageApiListRequest;
use App\Request\ImageApiSearchRequest;
use App\Request\VideoApiSuggestRequest;
use App\Service\SearchService;
use App\Service\SuggestService;
use App\Service\TagService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class SearchController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, SearchService $service, TagService $tagService)
    {
        $tagIds = $request->input('tags', []);
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $modelTags = $tagService->getTagsByModelType($request->input('type'), (int) $request->input('id'));
        $tagIds = array_merge($modelTags, $tagIds);
        $models = $service->search($tagIds, $page, $limit);
        $data = [];
        $data['models'] = $models;
        $data['test'] = env('TEST_IMG_URL');
        $path = '/api/search/list';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'keyword')]
    public function keyword(RequestInterface $request, SearchService $service)
    {
        $keyword = $request->input('keyword');
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $sortBy = (int) $request->input('sort_by');
        $isAsc = (int) $request->input('is_asc');
        $filter = Constants::FEED_TYPES[$request->input('filter')] ?? null;
        $models = $service->keyword($keyword, $page, $limit, $sortBy, $isAsc, $filter);
        $data = [];
        $data['models'] = $models;
        $path = '/api/search/keyword';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'suggest')]
    public function suggest(RequestInterface $request, SearchService $service, SuggestService $suggestService)
    {
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByMemberTag($userId);
        $models = $service->suggest($suggest, $page, $limit);
        $data = [];
        $data['models'] = $models;
        $path = '/api/search/suggest';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'popular')]
    public function popular(RequestInterface $request, SearchService $service)
    {
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $models = $service->popular($page, $limit);
        $data = [];
        $data['models'] = $models;
        $path = '/api/search/popular';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }
}
