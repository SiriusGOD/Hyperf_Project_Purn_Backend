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
use App\Request\ImageApiLikeRequest;
use App\Request\ImageApiListRequest;
use App\Request\ImageApiSearchRequest;
use App\Request\ImageApiSuggestRequest;
use App\Service\ImageService;
use App\Service\SuggestService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class ImageController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(ImageApiListRequest $request, ImageService $service)
    {
        $tagIds = $request->input('tags');
        $page = (int) $request->input('page', 0);
        $models = $service->getImages($tagIds, $page);
        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = Image::PAGE_PER;
        $path = '/api/image/list';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'search')]
    public function search(ImageApiSearchRequest $request, ImageService $service)
    {
        $keyword = $request->input('keyword');
        $page = (int) $request->input('page', 0);
        $models = $service->getImagesByKeyword($keyword, $page);
        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = Image::PAGE_PER;
        $path = '/api/image/search';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'suggest')]
    public function suggest(ImageApiSuggestRequest $request, SuggestService $suggestService, ImageService $service)
    {
        $page = (int) $request->input('page', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $models = $service->getImagesBySuggest($suggest, $page);
        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = Image::PAGE_PER;
        $path = '/api/image/suggest';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'like')]
    public function like(ImageApiLikeRequest $request)
    {
        $id = $request->input('id');
        $model = Image::find($id);
        ++$model->like;
        $model->save();
        return $this->success(['id' => $id, 'like' => $model->like]);
    }
}
