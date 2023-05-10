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
use App\Request\ProductApiRequest;
use App\Service\ProductService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
class ProductController extends AbstractController
{
    /**
     * 獲取上架中的商品列表.
     */
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, ProductService $service)
    {
        // 獲取使用者地區是否是台灣
        $isTW = $request->getAttribute('isTW');
        // member coin diamond
        $type = $request->input('type', 'coin');
        $result = $service->getListByType($type, $isTW);
        return $this->success($result);
    }

    /**
     * 獲取上架中的商品數.
     */
    #[RequestMapping(methods: ['POST'], path: 'count')]
    public function count(RequestInterface $request, ProductService $service)
    {
        $keyword = $request->input('keyword', '');
        $result = $service->getCount($keyword);
        $data = [
            'count' => $result,
        ];
        return $this->success($data);
    }

    /**
     * 獲取上架中的商品列表.
     */
    #[RequestMapping(methods: ['POST'], path: 'getMemberProductList')]
    public function getMemberList(ProductApiRequest $request, ProductService $service)
    {
        // 獲取使用者地區是否是台灣
        $isTW = $request->getAttribute('isTW');
        // member
        $result = $service->getListByMember($isTW);

        return $this->success($result);
    }
}
