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
use App\Model\Image;
use App\Model\ImageGroup;
use App\Request\ClickRequest;
use App\Request\ImageApiListRequest;
use App\Request\ImageApiSearchRequest;
use App\Request\ImageApiSuggestRequest;
use App\Service\ClickService;
use App\Service\ImageGroupService;
use App\Service\SuggestService;
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
        $data['page'] = $page;
        $data['step'] = ImageGroup::PAGE_PER;
        $path = '/api/image_group/list';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
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
        $data['page'] = $page;
        $data['step'] = Image::PAGE_PER;
        $path = '/api/image_group/search';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
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
        $data['page'] = $page;
        $data['step'] = Image::PAGE_PER;
        $path = '/api/image_group/suggest';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
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
}
