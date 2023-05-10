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
use App\Middleware\ApiEncryptMiddleware;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Model\MemberCategorization;
use App\Model\MemberCategorizationDetail;
use App\Service\MemberCategorizationService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class MemberCategorizationController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'create')]
    public function create(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $id = $service->createOrUpdateMemberCategorization([
            'name' => $request->input('name'),
            'member_id' => $memberId,
            'is_default' => $request->input('is_default'),
        ]);

        $ids = MemberCategorization::where('member_id', $memberId)
            ->where('id', '<>', $id)
            ->orderBy('hot_order')
            ->orderByDesc('id')
            ->get()
            ->pluck('id')
            ->toArray();

        $result = $ids;
        if ($request->input('is_default') == 1) {
            $service->setDefault($memberId, $id);
            array_unshift($result, $id);
        } else {
            $result = [];
            foreach ($ids as $key => $value) {
                if ($key == 1) {
                    $result[] = $id;
                }
                $result[] = $value;
            }
        }

        $service->updateOrder($result);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'update')]
    public function update(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();

        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $request->input('id'))
            ->where('is_first', 0)
            ->exists();

        if (! $exist) {
            return $this->error(trans('validation.exists', ['attribute' => 'id']), 400);
        }

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

    #[RequestMapping(methods: ['POST'], path: 'update/order')]
    public function updateOrder(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();

        $ids = $request->input('ids');
        $count = MemberCategorization::where('member_id', $memberId)
            ->whereIn('id', $ids)
            ->count();

        if ($count != count($ids)) {
            return $this->error(trans('validation.exists', ['attribute' => 'id']), 400);
        }

        $service->updateOrder($ids);
        $service->setDefault($memberId, $ids[0]);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/create')]
    public function createDetail(RequestInterface $request, MemberCategorizationService $service)
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
    public function list(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $page = $request->input('page', 0);
        $limit = $request->input('limit', Constants::DEFAULT_PAGE_PER);
        $isMain = $request->input('is_main', 0);
        $models = MemberCategorization::where('member_id', $memberId)
            ->orderByDesc('is_default')
            ->orderBy('hot_order')
            ->limit($limit)
            ->offset($page * $limit)
            ->get()
            ->toArray();

        $result = $models;

        if ($isMain) {
            $result = $service->isMain($memberId, $models);
        }

        $data = [
            'models' => $result,
        ];

        $path = '/api/member_categorization/list';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/list')]
    public function detail(RequestInterface $request, MemberCategorizationService $service)
    {
        $id = $request->input('member_categorization_id', 0);
        $memberId = auth()->user()->getId();

        $page = $request->input('page', 0);
        $limit = $request->input('limit', Constants::DEFAULT_PAGE_PER);

        if ($id == 0) {
            $data = [
                'models' => $service->getDefaultDetail([
                    'member_id' => $memberId,
                    'page' => $page,
                    'limit' => $limit,
                    'sort_by' => $request->input('sort_by'),
                    'is_asc' => $request->input('is_asc'),
                ]),
            ];

            $path = '/api/member_categorization/detail';
            $simplePaginator = new SimplePaginator($page, $limit, $path);
            $data = array_merge($data, $simplePaginator->render());
            return $this->success($data);
        }

        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $id)
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

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

    #[RequestMapping(methods: ['POST'], path: 'detail/update')]
    public function updateDetail(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $request->input('member_categorization_id', 0))
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $ids = $request->input('ids');
        $count = MemberCategorization::where('member_id', $memberId)
            ->join('member_categorization_details', 'member_categorizations.id', '=', 'member_categorization_details.member_categorization_id')
            ->whereIn('member_categorization_details.id', $ids)
            ->count();

        if ($count != count($ids)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $service->updateMemberCategorizationDetails([
            'ids' => $ids,
            'member_categorization_id' => $request->input('member_categorization_id'),
        ]);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/delete')]
    public function deleteDetail(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $ids = $request->input('ids');

        if (empty($ids)) {
            return $this->error(trans('validation.required', ['attribute' => 'ids']), 401);
        }

        $count = MemberCategorization::where('member_id', $memberId)
            ->join('member_categorization_details', 'member_categorizations.id', '=', 'member_categorization_details.member_categorization_id')
            ->whereIn('member_categorization_details.id', $ids)
            ->count();

        if ($count != count($ids)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        MemberCategorizationDetail::whereIn('id', $ids)->delete();

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'delete')]
    public function delete(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        MemberCategorization::where('member_id', $memberId)
            ->where('id', $request->input('id'))
            ->where('is_first', 0)
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
