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
use App\Service\SearchService;
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
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(RequestInterface $request, VideoService $service)
    {
        $data = [
            [
                'id' => 1,
                'name' => '大家都在看',
                'order' => 1,
            ],
            [
                'id' => 2,
                'name' => '專屬推薦',
                'order' => 2,
            ],
            [
                'id' => 3,
                'name' => '最新推薦',
                'order' => 3,
            ],
        ];
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'search')]
    public function getSearchList(RequestInterface $request, SearchService $service, SuggestService $suggestService)
    {
        $data = [];
        $page = $request->input('page', 0);
        $limit = $request->input('limit', 10);
        $id = $request->input('id', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $data['model'] = match ($id) {
            default => $service->popular($page, $limit),
            2 => $service->navigationSuggest($suggest, $page, $limit),
            3 => $service->navigationSuggestSortById($suggest, $page, $limit),
        };
        $path = '/api/navigation/search?id=' . $id . '&';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());

        return $this->success($data);
    }
}
