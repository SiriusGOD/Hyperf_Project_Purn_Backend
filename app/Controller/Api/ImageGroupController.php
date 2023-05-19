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
use App\Middleware\ApiEncryptMiddleware;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Model\CustomerService;
use App\Model\Image;
use App\Model\ImageGroup;
use App\Model\Member;
use App\Model\Product;
use App\Service\ClickService;
use App\Service\GenerateService;
use App\Service\ImageGroupService;
use App\Service\ImageService;
use App\Service\LikeService;
use App\Service\SearchService;
use App\Service\SuggestService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class ImageGroupController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, ImageGroupService $service, SearchService $searchService)
    {
        $tagIds = $request->input('tags');
        $page = (int) $request->input('page', 0);
        $models = $service->getImageGroups($tagIds, $page)->toArray();
        $result = $searchService->generateImageGroups([], $models);
        $data = [];
        $data['models'] = $result;
        $path = '/api/image_group/list';
        $simplePaginator = new SimplePaginator($page, CustomerService::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'search')]
    public function search(RequestInterface $request, ImageGroupService $service, SearchService $searchService)
    {
        $keyword = $request->input('keyword');
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', Image::PAGE_PER);
        $sortBy = (int) $request->input('sort_by');
        $isAsc = (int) $request->input('is_asc');
        $models = $service->getImageGroupsByKeyword($keyword, $page, $limit, $sortBy, $isAsc)->toArray();
        $result = $searchService->generateImageGroups([], $models);
        $data = [];
        $data['models'] = $result;
        $path = '/api/image_group/search';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'suggest')]
    public function suggest(RequestInterface $request, SuggestService $suggestService, ImageGroupService $service, SearchService $searchService)
    {
        $page = (int) $request->input('page', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByMemberTag($userId);
        $models = $service->getImageGroupsBySuggest($suggest, $page, ImageGroup::PAGE_PER);
        $result = $searchService->generateImageGroups([], $models);
        $data = [];
        $data['models'] = $result;
        $path = '/api/image_group/suggest';
        $simplePaginator = new SimplePaginator($page, CustomerService::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'click')]
    public function saveClick(RequestInterface $request, ClickService $service)
    {
        $id = (int) $request->input('id');
        $service->addClick(ImageGroup::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['POST'], path: 'click/popular')]
    public function getClickPopular(ClickService $service)
    {
        $result = $service->getPopularClick(ImageGroup::class);

        return $this->success($result);
    }

    #[RequestMapping(methods: ['POST'], path: 'like')]
    public function saveLike(RequestInterface $request, LikeService $service)
    {
        $id = (int) $request->input('id');
        $service->addLike(ImageGroup::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['POST'], path: 'pay_image')]
    public function getPayImage(RequestInterface $request, ImageGroupService $service, ImageService $imageService, GenerateService $generateService)
    {
        $id = (int) $request->input('id');
        $memberId = auth()->user()->getId();

        $base_service = di(\App\Service\BaseService::class);
        $ip = $base_service->getIp($request->getHeaders(), $request->getServerParams());

        $product = Product::where('expire', Product::EXPIRE['no'])
            ->where('type', ImageGroup::class)
            ->where('correspond_id', $id)
            ->first();

        if (empty($product)) {
            return $this->error(trans('validation.product_is_expire'), 400);
        }
        $isPay = $service->isPay($id, $memberId, $ip);

        if (! $isPay) {
            return $this->success([
                'is_pay' => 0,
                'product_id' => $product->id,
            ]);
        }

        return $this->success([
            'is_pay' => 1,
            'product_id' => $product->id,
        ]);
    }

    #[RequestMapping(methods: ['POST'], path: 'pay_method')]
    public function getPayMethod(RequestInterface $request)
    {
        $id = (int) $request->input('id');
        $memberId = auth()->user()->getId();

        $member = Member::find($memberId);

        return $this->success([
            'diamond' => [
                'price' => "1",
                'member_diamond_coin' => (string) $member->diamond_coins,
            ],
            'coin' => [
                'price' => null,
                'member_coin' => (string) $member->coins,
            ],
        ]);
    }
}
