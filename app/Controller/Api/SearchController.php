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

#[Controller]
class SearchController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(ImageApiListRequest $request, SearchService $service, TagService $tagService)
    {
        $tagIds = $request->input('tags',[]);
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $modelTags = $tagService->getTagsByModelType($request->input('type'), (int) $request->input('id'));
        $tagIds = array_merge($modelTags, $tagIds);
        $models = $service->search($tagIds, $page, $limit);
        $data = [];
        $data['models'] = $models;
        $path = '/api/search/list';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'keyword')]
    public function keyword(ImageApiSearchRequest $request, SearchService $service)
    {
        $keyword = $request->input('keyword');
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $models = $service->keyword($keyword, $page, $limit);
        $data = [];
        $data['models'] = $models;
        $path = '/api/search/keyword';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'suggest')]
    public function suggest(VideoApiSuggestRequest $request, SearchService $service, SuggestService $suggestService)
    {
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $models = $service->suggest($suggest, $page, $limit);
        $data = [];
        $data['models'] = $models;
        $path = '/api/search/suggest';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'popular')]
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
