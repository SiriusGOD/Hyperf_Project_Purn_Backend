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
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Model\MemberCategorization;
use App\Model\Navigation;
use App\Request\NavigationDetailRequest;
use App\Service\NavigationService;
use App\Service\SuggestService;
use App\Service\VideoService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class NavigationController extends AbstractController
{
    public const DEFAULT_MATCH_COUNT = 3;
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, VideoService $service)
    {
        $memberId = auth()->user()->getId();
        $data = Navigation::select('id', 'name', 'hot_order as order')->orderBy('hot_order')->get()->toArray();
        $memberNavs = MemberCategorization::where('member_id', $memberId)
            ->select('id', 'name', 'hot_order as order')
            ->where('is_first', 0)
            ->orderBy('hot_order')
            ->orderBy('id')
            ->get();

        $count = count($data);

        $result = [];
        foreach ($memberNavs as $nav) {
            $nav['id'] = $count + $nav['id'];
            $result[] = $nav;
        }

        $result = array_merge($data, $result);

        return $this->success($result);
    }

    #[RequestMapping(methods: ['POST'], path: 'search')]
    public function getSearchList(RequestInterface $request, NavigationService $service, SuggestService $suggestService)
    {
        $data = [];
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $id = (int) $request->input('id', 0);
        $userId = (int) auth()->user()->getId();
        if ($id > 3) {
            $suggest = $suggestService->getTagProportionByMemberCategorization($id - self::DEFAULT_MATCH_COUNT);
        } else {
            $suggest = $suggestService->getTagProportionByMemberTag($userId);
        }
        $data['models'] = match ($id) {
            1 => $service->navigationPopular($suggest, $page, $limit),
            2 => $service->navigationSuggest($suggest, $page, $limit),
            3 => $service->navigationSuggestSortById($suggest, $page, $limit),
            default => $service->navigationSuggestByMemberCategorization($suggest, $page, $limit, $userId)
        };
        $path = '/api/navigation/search?id=' . $id . '&';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());

        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'detail')]
    public function detail(RequestInterface $request, NavigationService $service, SuggestService $suggestService)
    {
        $data = [];
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', 10);
        $navId = (int) $request->input('nav_id', 0);
        $type = $request->input('type');
        $id = (int) $request->input('type_id');
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByMemberTag($userId);
        $data['models'] = $service->navigationDetail($suggest, $navId, $type, $id, $page, $limit);
        $path = '/api/navigation/detail?id=' . $id . '&';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());

        return $this->success($data);
    }
}
