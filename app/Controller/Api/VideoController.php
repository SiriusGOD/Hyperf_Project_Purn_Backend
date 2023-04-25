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
use App\Request\GetPayImageRequest;
use App\Request\VideoApiSuggestRequest;
use App\Service\ActorService;
use App\Service\ClickService;
use App\Service\LikeService;
use App\Service\RedeemService;
use App\Service\SuggestService;
use App\Service\TagService;
use App\Service\VideoService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;

#[Controller]
class VideoController extends AbstractController
{
    protected LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        // 第一個引數對應日誌的 name, 第二個引數對應 config/autoload/logger.php 內的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }
  
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
    public function videoRedeem(RequestInterface $request, RedeemService $redeemService)
    {
        $videoId = $request->input('video_id');
        $code = $request->input('redeem_code');
        $userId = auth('jwt')->user()->getId();
        if (! $userId) {
            return $this->error(Apicode::USER_NOT_FOUND_MSG, Apicode::USER_NOT_FOUND);
        }
        $redeemService->redeemVideo($videoId, $userId, $code);
        return $this->success([]);
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
        if(env("APP_ENV")=="develop"){ 
          $this->logger->info(json_encode($data));
        } 
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

    #[RequestMapping(methods: ['POST'], path: 'like')]
    public function saveLike(ClickRequest $request, LikeService $service)
    {
        $id = (int) $request->input('id');
        $service->addLike(Video::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['GET'], path: 'pay_video')]
    public function getPayVideo(GetPayImageRequest $request, VideoService $service)
    {
        $id = (int) $request->input('id');
        $memberId = auth()->user()->getId();

        if (! $service->isPay($id, $memberId)) {
            return $this->error(trans('validation.is_not_pay'));
        }

        $data = $service->getPayVideo($id)->toArray();

        return $this->success($data);
    }
}
