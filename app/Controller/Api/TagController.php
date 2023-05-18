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
use App\Service\TagGroupService;
use App\Service\TagService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Middleware\ApiEncryptMiddleware;
use Carbon\Carbon;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
class TagController extends AbstractController
{
    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, TagService $service)
    {
        $data = $service->getTags();
        return $this->success(['models' => $data->toArray()]);
    }

    #[RequestMapping(methods: ['POST'], path: 'popular')]
    public function popular(RequestInterface $request, TagService $service)
    {
        $data = $service->getPopularTag();
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'groupList')]
    public function groupList(RequestInterface $request, TagGroupService $service)
    {
        $data = $service->getTags();
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'searchGroupTags')]
    public function searchGroupTags(RequestInterface $request, TagGroupService $service)
    {
        $group_id = $request->input('group_id');
        $data = $service->searchGroupTags($group_id);
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'getTagDetail')]
    public function getTagDetail(RequestInterface $request, TagService $service)
    {
        $tag_id = $request->input('tag_id');
        $data = $service->getTagDetail($tag_id);
        return $this->success(['models' => $data]);
    }

    #[RequestMapping(methods: ['POST'], path: 'search')]
    public function search(RequestInterface $request, TagService $service)
    {
        $id = $request->input('tag_id', 0);
        $page = $request->input('page', 0);
        $limit = $request->input('limit', Constants::DEFAULT_PAGE_PER);
        $filter = Constants::FEED_TYPES[$request->input('filter')] ?? null;
        $result = $service->searchByTagId([
            'id' => $id,
            'page' => $page,
            'limit' => $limit,
            'sort_by' => $request->input('sort_by'),
            'is_asc' => $request->input('is_asc'),
            'filter' => $filter,
        ]);

        // 整理
        // $arr = [];
        // foreach ($result as $key => $value) {
        //     $res = array(
        //         'id' => $value['id'],
        //         'name' => $value['title'],
        //         'description' => $value['description'],
        //         'type' => $value['model_type'],
        //         'thumbnail' => $value['cover_thumb'] ?? $value['thumbnail'],
        //         'height' => $value['thumb_height'] ?? ($value['height'] ?? 0),
        //         'width' => $value['thumb_width'] ?? ($value['width'] ?? 0),
        //         'price' => $value['point'],
        //         'image_count' => $value['image_count'] ?? 0
        //     );
        //     if(!empty($value['duration'])){
        //         // 將秒數轉換為分鐘和秒
        //         $minutes = floor($value['duration'] / 60);
        //         $seconds = $value['duration'] % 60;
        //         $res['video_duration'] = sprintf('%02d:%02d', $minutes, $seconds);   
        //     }else{
        //         $res['video_duration'] = null;
        //     }

        //     if($value['model_type'] == 'video'){
        //         $res['pay_type'] = $value['is_free'];
        //     }else{
        //         $res['pay_type'] = $value['pay_type'];
        //     }
        //     array_push($arr, $res);
        // }

        $data = [
            'models' => $result,
        ];

        $path = '/api/actor/search';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }
}
