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

use App\Constants\Apicode;
use App\Constants\Constants;
use App\Controller\AbstractController;
use App\Model\Video;
use App\Request\ClickRequest;
use App\Request\VideoApiSuggestRequest;
use App\Service\ActorService;
use App\Service\ClickService;
use App\Service\SuggestService;
use App\Service\TagService;
use App\Service\VideoService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class VideoController extends AbstractController
{
    #[RequestMapping(methods: ['GET'], path: 'list')]
    public function list(RequestInterface $request, VideoService $service)
    {
        $tagIds = $request->input('tags', []);
        $page = (int) $request->input('page', 0);
        $data = [];
        $data['models'] = $service->getVideos($tagIds, $page);
        $data['page'] = $page;
        $data['step'] = Constants::DEFAULT_PAGE_PER;
        $path = '/api/image/list';
        $data['next'] = $path . '?page=' . ($page + 1);
        if ($page == 1) {
            $data['prev'] = '';
        } else {
            $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        }
        return $this->success($data);
    }

    // 影片兌換
    #[RequestMapping(methods: ['POST'], path: 'videoRedeem')]
    public function videoRedeem(RequestInterface $request, VideoService $videoService)
    {
        $videoId = $request->input('video_id');
        $code = $request->input('redeem_code');
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $videoService->redeemVideo($videoId, $userId, $code);
        return $this->success([]);
    }

    // 儲存影片
    #[RequestMapping(methods: ['POST'], path: 'stageVideo')]
    public function stageVideo(RequestInterface $request, VideoService $videoService)
    {
        $videoId = $request->input('video_id');
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $videoService->storeStageVideo($videoId, $userId);
        return $this->success([]);
    }

    // 儲存影片
    #[RequestMapping(methods: ['GET'], path: 'stagelist')]
    public function stageList(RequestInterface $request, VideoService $service)
    {
        $videoId = $request->input('video_id');
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $page = (int) $request->input('page', 0);
        $data = [];
        $data['models'] = $service->myStageVideo($userId, $page);
        $data['page'] = $page;
        $data['step'] = Constants::DEFAULT_PAGE_PER;
        $path = '/api/image/list';
        $data['next'] = $path . '?page=' . ($page + 1);
        if ($page == 1) {
            $data['prev'] = '';
        } else {
            $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        }
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'count')]
    public function count(VideoService $service)
    {
        $result = $service->getVideoCount();
        return $this->success([
            'count' => (int) $result,
        ]);
    }

    /**
     * 回調匯入資料.
     */
    #[RequestMapping(methods: ['POST'], path: 'data')]
    public function data(RequestInterface $request, VideoService $VideoService, TagService $tagService, ActorService $actorService)
    {
        $data = $request->all();
        $video = $VideoService->storeVideo($data);
        $tagService->videoCorrespondTag($data, $video->id);
        $actorService->videoCorrespondActor($data, $video->id);
        return $this->success([$video]);
    }

    #[RequestMapping(methods: ['GET'], path: 'find')]
    public function find(RequestInterface $request, VideoService $service)
    {
        $id = $request->input('id', 0);
        $data['models'] = $service->find($id);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'search')]
    public function search(RequestInterface $request, VideoService $service)
    {
        $title = $request->input('title');
        $length = $request->input('length', 0);
        $compare = $request->input('compare', 0);
        $page = (int) $request->input('page', 0);
        if (empty($title) || strlen($title) == 0) {
            $result = ['message' => 'title 不得為空'];
            return $this->success($result);
        }
        $data['models'] = $service->searchVideo($title, $compare, $length, $page);
        $data['page'] = $page;
        $data['step'] = Constants::DEFAULT_PAGE_PER;
        $path = '/api/video/search';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['GET'], path: 'suggest')]
    public function suggest(VideoApiSuggestRequest $request, VideoService $service, SuggestService $suggestService)
    {
        $page = (int) $request->input('page', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByUser($userId);
        $models = $service->getVideosBySuggest($suggest, $page);
        $data = [];
        $data['models'] = $models;
        $data['page'] = $page;
        $data['step'] = Constants::DEFAULT_PAGE_PER;
        $path = '/api/video/suggest';
        $data['next'] = $path . '?page=' . ($page + 1);
        $data['prev'] = $path . '?page=' . (($page == 0 ? 1 : $page) - 1);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'click')]
    public function saveClick(ClickRequest $request, ClickService $service)
    {
        $id = (int) $request->input('id');
        $service->addClick(Video::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['GET'], path: 'click/popular')]
    public function getClickPopular(ClickService $service)
    {
        $result = $service->getPopularClick(Video::class);

        return $this->success($result);
    }
}
