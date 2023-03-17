<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Request\ImageApiRequest;
use App\Request\TagRequest;
use App\Service\ImageService;
use App\Service\TagService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

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
        $data = $service->getImages($tagIds);
        return $this->success($data->toArray());
    }
}
