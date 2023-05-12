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
use App\Model\ImageGroup;
use App\Model\MemberCategorization;
use App\Model\MemberCategorizationDetail;
use App\Model\Video;
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
        $id = $request->input('id', 0) - NavigationController::DEFAULT_MATCH_COUNT;

        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $id)
            ->where('is_first', 0)
            ->exists();

        if (! $exist) {
            return $this->error(trans('validation.exists', ['attribute' => 'id']), 400);
        }

        $effectId = $service->createOrUpdateMemberCategorization([
            'id' => $id,
            'name' => $request->input('name'),
            'member_id' => $memberId,
            'hot_order' => $request->input('hot_order'),
        ]);

        if ($request->input('is_default') == 1) {
            $service->setDefault($memberId, $effectId);
        }

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'update/order')]
    public function updateOrder(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();

        $ids = $request->input('ids');

        $idsArr = [];
        foreach ($ids as $id) {
            $idsArr[] = $id - NavigationController::DEFAULT_MATCH_COUNT;
        }

        $count = MemberCategorization::where('member_id', $memberId)
            ->whereIn('id', $idsArr)
            ->count();

        if ($count != count($idsArr)) {
            return $this->error(trans('validation.exists', ['attribute' => 'id']), 400);
        }

        $service->updateOrder($idsArr);
        $service->setDefault($memberId, $idsArr[0]);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/create')]
    public function createDetail(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $id = $request->input('member_categorization_id', 0) - NavigationController::DEFAULT_MATCH_COUNT;
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $id)
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $type = MemberCategorizationDetail::TYPES[$request->input('type')];
        $service->createMemberCategorizationDetail([
            'member_categorization_id' => $id,
            'type' => $type,
            'type_id' => $request->input('type_id'),
        ]);

        $service->updateCache($memberId, $type);

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
        $rows = [];
        foreach ($models as $model) {
            $model['id'] = $model['id'] + NavigationController::DEFAULT_MATCH_COUNT;
            $rows[] = $model;
        }

        $result = $rows;

        if ($isMain) {
            $result = $service->isMain($memberId, $rows);
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
        $id = (int) $request->input('member_categorization_id', 0);
        $memberId = auth()->user()->getId();

        $page = $request->input('page', 0);
        $limit = $request->input('limit', Constants::DEFAULT_PAGE_PER);
        $filter = MemberCategorizationDetail::TYPES[$request->input('filter')] ?? null;

        if ($id == 0) {
            $data = [
                'models' => $service->getDefaultDetail([
                    'member_id' => $memberId,
                    'page' => $page,
                    'limit' => $limit,
                    'sort_by' => $request->input('sort_by'),
                    'is_asc' => $request->input('is_asc'),
                    'filter' => $filter,
                ]),
            ];

            $path = '/api/member_categorization/detail';
            $simplePaginator = new SimplePaginator($page, $limit, $path);
            $data = array_merge($data, $simplePaginator->render());
            return $this->success($data);
        }

        $id = $id - NavigationController::DEFAULT_MATCH_COUNT;
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $id)
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $models = $service->getDetails([
            'id' => $id,
            'page' => $page,
            'limit' => $limit,
            'sort_by' => $request->input('sort_by'),
            'is_asc' => $request->input('is_asc'),
            'filter' => $filter,
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
        $id = $request->input('member_categorization_id', 0) - NavigationController::DEFAULT_MATCH_COUNT;
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $id)
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
            'member_categorization_id' => $id,
        ]);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/update/feed')]
    public function updateDetailWithType(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $type = MemberCategorizationDetail::TYPES[$request->input('type')] ?? null;
        $typeId = $request->input('type_id');

        if (empty($type) or empty($typeId)) {
            return $this->error(trans('validation.required', ['attribute' => 'type or type_id']), 400);
        }

        $memberCategorizationId = $request->input('member_categorization_id', 0) - NavigationController::DEFAULT_MATCH_COUNT;
        $exist = MemberCategorization::where('member_id', $memberId)
            ->where('id', $memberCategorizationId)
            ->exists();

        if (empty($exist)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $model = MemberCategorization::where('member_id', $memberId)
            ->join('member_categorization_details', 'member_categorizations.id', '=', 'member_categorization_details.member_categorization_id')
            ->where('member_categorization_details.type', $type)
            ->where('member_categorization_details.type_id', $typeId)
            ->first();

        if (empty($model)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        $service->updateMemberCategorizationDetails([
            'ids' => [$model->id],
            'member_categorization_id' => $memberCategorizationId,
        ]);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/delete')]
    public function deleteDetail(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $ids = $request->input('ids');

        if (empty($ids)) {
            return $this->error(trans('validation.required', ['attribute' => 'ids']), 400);
        }

        $count = MemberCategorization::where('member_id', $memberId)
            ->join('member_categorization_details', 'member_categorizations.id', '=', 'member_categorization_details.member_categorization_id')
            ->whereIn('member_categorization_details.id', $ids)
            ->count();

        if ($count != count($ids)) {
            return $this->error(trans('validation.authorize'), 401);
        }

        MemberCategorizationDetail::whereIn('id', $ids)->delete();

        $service->updateCache($memberId, ImageGroup::class);
        $service->updateCache($memberId, Video::class);

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'detail/delete/feed')]
    public function deleteDetailWithType(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $id = $request->input('type_id');
        $type = MemberCategorizationDetail::TYPES[$request->input('type')] ?? null;

        if (empty($id) or empty($type)) {
            return $this->error(trans('validation.required', ['attribute' => 'id']), 400);
        }

        $exist = MemberCategorization::where('member_id', $memberId)
            ->join('member_categorization_details', 'member_categorizations.id', '=', 'member_categorization_details.member_categorization_id')
            ->where('member_categorization_details.type', $type)
            ->where('member_categorization_details.type_id', $id)
            ->exists();

        if (! $exist) {
            return $this->error(trans('validation.authorize'), 401);
        }

        MemberCategorizationDetail::where('type', $type)->where('type_id', $id)->delete();

        match ($type) {
            ImageGroup::class => $service->updateCache($memberId, ImageGroup::class),
            default => $service->updateCache($memberId, Video::class),
        };

        return $this->success();
    }

    #[RequestMapping(methods: ['POST'], path: 'delete')]
    public function delete(RequestInterface $request, MemberCategorizationService $service)
    {
        $memberId = auth()->user()->getId();
        $id = $request->input('id') - NavigationController::DEFAULT_MATCH_COUNT;
        MemberCategorization::where('member_id', $memberId)
            ->where('id', $id)
            ->where('is_first', 0)
            ->delete();

        MemberCategorizationDetail::where('member_categorization_id', $id)->delete();

        $service->updateCache($memberId, ImageGroup::class);
        $service->updateCache($memberId, Video::class);

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

    #[RequestMapping(methods: ['POST'], path: 'detail/count')]
    public function getDetailCount(RequestInterface $request, MemberCategorizationService $service)
    {
        $id = (int) $request->input('id', 0);
        $memberId = auth()->user()->getId();

        if($id == 0) {
            return $this->success([
                'video_count' => $service->getDefaultCount($memberId, Video::class),
                'image_group_count' => $service->getDefaultCount($memberId, ImageGroup::class),
            ]);
        }

        $id = $id - NavigationController::DEFAULT_MATCH_COUNT;
        return $this->success([
            'video_count' => $service->getCount($id, Video::class),
            'image_group_count' => $service->getCount($id, ImageGroup::class),
        ]);
    }
}
