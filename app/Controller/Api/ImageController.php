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
use App\Model\Image;
use App\Request\ClickRequest;
use App\Request\ImageApiListRequest;
use App\Request\ImageApiSearchRequest;
use App\Request\ImageApiSuggestRequest;
use App\Service\ClickService;
use App\Service\ImageService;
use App\Service\LikeService;
use App\Service\SuggestService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class ImageController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, ImageService $service)
    {
        $tagIds = $request->input('tags');
        $page = (int) $request->input('page', 0);
        $models = $service->getImages($tagIds, $page);
        $data = [];
        $data['models'] = $models;
        $path = '/api/customer_service/list';
        $simplePaginator = new SimplePaginator($page, Image::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'search')]
    public function search(RequestInterface $request, ImageService $service)
    {
        $keyword = $request->input('keyword');
        $page = (int) $request->input('page', 0);
        $models = $service->getImagesByKeyword($keyword, $page);
        $data = [];
        $data['models'] = $models;
        $path = '/api/image/search';
        $simplePaginator = new SimplePaginator($page, Image::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'suggest')]
    public function suggest(RequestInterface $request, SuggestService $suggestService, ImageService $service)
    {
        $page = (int) $request->input('page', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByMemberTag($userId);
        $models = $service->getImagesBySuggest($suggest, $page);
        $data = [];
        $data['models'] = $models;
        $path = '/api/image/suggest';
        $simplePaginator = new SimplePaginator($page, Image::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'click')]
    public function saveClick(RequestInterface $request, ClickService $service)
    {
        $id = (int) $request->input('id');
        $service->addClick(Image::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['POST'], path: 'click/popular')]
    public function getClickPopular(RequestInterface $service)
    {
        $result = $service->getPopularClick(Image::class);

        return $this->success($result);
    }

    #[RequestMapping(methods: ['POST'], path: 'like')]
    public function saveLike(RequestInterface $request, LikeService $service)
    {
        $id = (int) $request->input('id');
        $service->addLike(Image::class, $id);
        return $this->success([]);
    }
}
