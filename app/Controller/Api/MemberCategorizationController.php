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
use App\Model\MemberCategorization;
use App\Model\MemberCategorizationDetail;
use App\Request\MemberCategorizationCreateRequest;
use App\Request\MemberCategorizationDeleteRequest;
use App\Request\MemberCategorizationDetailCreateRequest;
use App\Request\MemberCategorizationDetailDeleteRequest;
use App\Request\MemberCategorizationDetailUpdateRequest;
use App\Request\MemberCategorizationUpdateRequest;
use App\Service\MemberCategorizationService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
#[Middleware(ApiAuthMiddleware::class)]
class MemberCategorizationController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'create')]
    public function create(MemberCategorizationCreateRequest $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $id = $service->createOrUpdateMemberCategorization([
            'name' => $request->input('name'),
            'member_id' => $memberId,
            'hot_order' => $request->input('hot_order'),
            'is_default' => $request->input('is_default'),
        ]);

        if ($request->input('is_default') == 1) {
            $service->setDefault($memberId, $id);
        }

        return $this->success();
    }

    #[RequestMapping(methods: ['PUT'], path: 'update')]
    public function update(MemberCategorizationUpdateRequest $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $id = $service->createOrUpdateMemberCategorization([
            'id' => $request->input('id', 0),
            'name' => $request->input('name'),
            'member_id' => $memberId,
            'hot_order' => $request->input('hot_order'),
        ]);

        if ($request->input('is_default') == 1) {
            $service->setDefault($memberId, $id);
        }

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/create')]
    public function createDetail(MemberCategorizationDetailCreateRequest $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $request->input('member_categorization_id', 0))
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $service->createMemberCategorizationDetail([
            'member_categorization_id' => $request->input('member_categorization_id'),
            'type' => MemberCategorizationDetail::TYPES[$request->input('type')],
            'type_id' => $request->input('type_id'),
        ]);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request)
    {
        $memberId = auth()->user()->getId();
        $page = $request->input('page', 0);
        $limit = $request->input('limit', Constants::DEFAULT_PAGE_PER);
        $models = MemberCategorization::where('member_id', $memberId)
            ->orderByDesc('is_default')
            ->orderBy('hot_order')
            ->limit($limit)
            ->offset($page * $limit)
            ->get()
            ->toArray();

        $data = [
            'models' => $models,
        ];

        $path = '/api/member_categorization/list';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/list')]
    public function detail(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $request->input('member_categorization_id', 0))
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $page = $request->input('page', 0);
        $limit = $request->input('limit', Constants::DEFAULT_PAGE_PER);
        $models = $service->getDetails([
            'id' => $request->input('member_categorization_id', 0),
            'page' => $page,
            'limit' => $limit,
            'sort_by' => $request->input('sort_by'),
            'is_asc' => $request->input('is_asc'),
        ]);

        $data = [
            'models' => $models,
        ];

        $path = '/api/member_categorization/detail';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['PUT'], path: 'detail/update')]
    public function updateDetail(MemberCategorizationDetailUpdateRequest $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $request->input('member_categorization_id', 0))
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $service->updateMemberCategorizationDetail([
            'id' => $request->input('member_categorization_id'),
            'member_categorization_id' => $request->input('member_categorization_id'),
        ]);

        return $this->success();
    }

    #[RequestMapping(methods: ['DELETE'], path: 'detail/delete')]
    public function deleteDetail(MemberCategorizationDetailDeleteRequest $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $detail = MemberCategorizationDetail::find($request->input('id'));
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $detail->member_categorization_id)
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $detail->delete();

        return $this->success();
    }

    #[RequestMapping(methods: ['DELETE'], path: 'delete')]
    public function delete(MemberCategorizationDeleteRequest $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        MemberCategorization::where('member_id', $memberId)
            ->where('id', $request->input('id'))
            ->delete();

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/exist')]
    public function isExist(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $type = MemberCategorizationDetail::TYPES[$request->input('type')];
        $ids = MemberCategorization::where('member_id', $memberId)->get()->pluck('id')->toArray();
        $count = MemberCategorizationDetail::whereIn('member_categorization_id', $ids)
            ->where('type', $type)
            ->where('type_id', $request->input('type_id'))
            ->count();
        $exist = 0;
        if ($count > 0) {
            $exist = 1;
        }

        return $this->success([
            'is_exist' => $exist,
        ]);
    }
}
