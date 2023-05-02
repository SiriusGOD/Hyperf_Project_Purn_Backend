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
use App\Model\Navigation;
use App\Request\NavigationDetailRequest;
use App\Service\NavigationService;
use App\Service\SuggestService;
use App\Service\VideoService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

// TODO 完成導航列
#[Controller]
class NavigationController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, VideoService $service)
    {
        $data = Navigation::select('id', 'name', 'hot_order as order')->orderBy('hot_order')->get()->toArray();
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'search')]
    public function getSearchList(RequestInterface $request, NavigationService $service, SuggestService $suggestService)
    {
        $data = [];
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $id = (int) $request->input('id', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $data['model'] = match ($id) {
            default => $service->navigationPopular($suggest, $page, $limit),
            2 => $service->navigationSuggest($suggest, $page, $limit),
            3 => $service->navigationSuggestSortById($suggest, $page, $limit),
        };
        $path = '/api/navigation/search?id=' . $id . '&';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());

        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'detail')]
    public function detail(NavigationDetailRequest $request, NavigationService $service, SuggestService $suggestService)
    {
        $data = [];
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $navId = (int) $request->input('nav_id', 0);
        $type = $request->input('type');
        $id = (int) $request->input('type_id');
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $data['model'] = $service->navigationDetail($suggest, $navId, $type, $id, $page, $limit);
        $path = '/api/navigation/detail?id=' . $id . '&';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());

        return $this->success($data);
    }
}
