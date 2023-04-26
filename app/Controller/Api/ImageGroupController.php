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
use App\Model\CustomerService;
use App\Model\ImageGroup;
use App\Request\ClickRequest;
use App\Request\GetPayImageRequest;
use App\Request\ImageApiListRequest;
use App\Request\ImageApiSearchRequest;
use App\Request\ImageApiSuggestRequest;
use App\Service\ClickService;
use App\Service\ImageGroupService;
use App\Service\ImageService;
use App\Service\LikeService;
use App\Service\SuggestService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class ImageGroupController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(ImageApiListRequest $request, ImageGroupService $service)
    {
        $tagIds = $request->input('tags');
        $page = (int) $request->input('page', 0);
        $models = $service->getImageGroups($tagIds, $page);
        $data = [];
        $data['models'] = $models;
        $path = '/api/image_group/list';
        $simplePaginator = new SimplePaginator($page, CustomerService::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'search')]
    public function search(ImageApiSearchRequest $request, ImageGroupService $service)
    {
        $keyword = $request->input('keyword');
        $page = (int) $request->input('page', 0);
        $models = $service->getImageGroupsByKeyword($keyword, $page);
        $data = [];
        $data['models'] = $models;
        $path = '/api/image_group/search';
        $simplePaginator = new SimplePaginator($page, CustomerService::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'suggest')]
    public function suggest(ImageApiSuggestRequest $request, SuggestService $suggestService, ImageGroupService $service)
    {
        $page = (int) $request->input('page', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $models = $service->getImageGroupsBySuggest($suggest, $page);
        $data = [];
        $data['models'] = $models;
        $path = '/api/image_group/suggest';
        $simplePaginator = new SimplePaginator($page, CustomerService::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'click')]
    public function saveClick(ClickRequest $request, ClickService $service)
    {
        $id = (int) $request->input('id');
        $service->addClick(ImageGroup::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['GET'], path: 'click/popular')]
    public function getClickPopular(ClickService $service)
    {
        $result = $service->getPopularClick(ImageGroup::class);

        return $this->success($result);
    }

    #[RequestMapping(methods: ['POST'], path: 'like')]
    public function saveLike(ClickRequest $request, LikeService $service)
    {
        $id = (int) $request->input('id');
        $service->addLike(ImageGroup::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['GET'], path: 'pay_image')]
    public function getPayImage(GetPayImageRequest $request, ImageGroupService $service, ImageService $imageService)
    {
        $id = (int) $request->input('id');
        $memberId = auth()->user()->getId();

        if (! $service->isPay($id, $memberId)) {
            return $this->error(trans('validation.is_not_pay'), 400);
        }

        $data = $imageService->getImagesByImageGroup($id)->toArray();

        return $this->success($data);
    }
}
