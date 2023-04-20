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
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class SearchController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(ImageApiListRequest $request, SearchService $service)
    {
        $tagIds = $request->input('tags');
        $page = (int) $request->input('page', 0);
        $models = $service->search($tagIds, $page);
        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = 100;
        $path = '/api/search/list';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'keyword')]
    public function keyword(ImageApiSearchRequest $request, SearchService $service)
    {
        $keyword = $request->input('keyword');
        $page = (int) $request->input('page', 0);
        $models = $service->keyword($keyword, $page);
        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = 100;
        $path = '/api/search/keyword';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'suggest')]
    public function suggest(VideoApiSuggestRequest $request, SearchService $service, SuggestService $suggestService)
    {
        $page = (int) $request->input('page', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $models = $service->suggest($suggest, $page);
        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = 100;
        $path = '/api/search/suggest';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'popular')]
    public function popular(RequestInterface $request, SearchService $service)
    {
        $page = (int) $request->input('page', 0);
        $models = $service->popular($page);
        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = 100;
        $path = '/api/search/popular';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }
}
