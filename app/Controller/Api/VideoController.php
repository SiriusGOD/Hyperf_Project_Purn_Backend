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
use App\Request\VideoApiSuggestRequest;
use App\Service\ActorService;
use App\Service\SuggestService;
use App\Service\TagService;
use App\Service\VideoService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use App\Constants\Constants;

/**
 * @Controller
 */
class VideoController extends AbstractController
{
    /**
     * @RequestMapping(path="list", methods="get")
     */
    public function list(RequestInterface $request, VideoService $service)
    {
        $tagIds = $request->input('tags',[]);
        $page = (int) $request->input('page', 1);
        $data = [];
        $data['models'] =$service->getVideos($tagIds, $page);
        $data['page'] = $page;
        $data['step'] = Constants::DEFAULT_PAGE_PER;
        $path = '/api/image/list';
        $data['next'] = $path . '?page=' . ($page + 1);
        if( $page == 1 ){
          $data['prev'] = "";
        }else{
          $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        }
        return $this->success($data);

    }

    /**
     * @RequestMapping(path="count", methods="get")
     */
    public function count(VideoService $service)
    {
        $result = $service->getVideoCount();
        return $this->success([$result]);
    }

    /**
     * 回調匯入資料.
     * @RequestMapping(path="data", methods="post")
     */
    public function data(RequestInterface $request, VideoService $VideoService, TagService $tagService, ActorService $actorService)
    {
        $data = $request->all();
        $video = $VideoService->storeVideo($data);
        $tagService->videoCorrespondTag($data, $video->id);
        $actorService->videoCorrespondActor($data, $video->id);
        return $this->success([$video]);
    }

    /**
     * @RequestMapping(path="search", methods="get")
     */
    public function search(RequestInterface $request, VideoService $service)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $name = $request->input('name', '');
        $length = $request->input('length', 0);
        $compare = $request->input('compare', 0);
        if (empty($name) || strlen($name) == 0) {
            $result = ['message' => 'name 不得為空'];
            return $this->success($result);
        }
        $result = $service->searchVideo($name, $compare, $length, $offset, $limit);
        return $this->success([$result]);
    }

    /**
     * @RequestMapping(path="suggest", methods="get")
     */
    public function suggest(VideoApiSuggestRequest $request, VideoService $service, SuggestService $suggestService)
    {
        $page = (int) $request->input('page', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $models = $service->getVideosBySuggest($suggest, $page);

        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = Image::PAGE_PER;
        $path = '/api/video/suggest';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);

        return $this->success($data);
    }
}
