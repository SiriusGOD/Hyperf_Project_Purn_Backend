<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Model\Image;
use App\Request\ImageApiRequest;
use App\Request\TagRequest;
use App\Service\ImageService;
use App\Service\TagService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;

/**
 * @Controller()
 */
class ImageController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(ImageApiRequest $request, ImageService $service)
    {
        $tagIds = $request->input('tags');
        $page = $request->input('page', 0);
        $models = $service->getImages($tagIds, $page);

        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = Image::PAGE_PER;
        $path = '/api/image/list';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . ($page - 1);
        return $this->success($data);
    }
}
