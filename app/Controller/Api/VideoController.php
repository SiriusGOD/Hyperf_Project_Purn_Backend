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
use App\Middleware\ApiEncryptMiddleware;
use App\Middleware\Auth\ApiAuthMiddleware;
use App\Model\ImageGroup;
use App\Model\Member;
use App\Model\Product;
use App\Model\Video;
use App\Request\VideoApiSearchRequest;
use App\Service\ActorService;
use App\Service\ClickService;
use App\Service\GenerateService;
use App\Service\LikeService;
use App\Service\RedeemService;
use App\Service\SuggestService;
use App\Service\TagService;
use App\Service\VideoService;
use App\Util\SimplePaginator;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

#[Controller]
#[Middleware(ApiEncryptMiddleware::class)]
#[Middleware(ApiAuthMiddleware::class)]
class VideoController extends AbstractController
{
    protected LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        // 第一個引數對應日誌的 name, 第二個引數對應 config/autoload/logger.php 內的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    #[RequestMapping(methods: ['POST'], path: 'list')]
    public function list(RequestInterface $request, VideoService $service)
    {
        $tagIds = $request->input('tags', []);
        $page = (int) $request->input('page', 0);
        $data = [];
        $data['models'] = $service->getVideos($tagIds, $page);
        $path = '/api/video/list';
        $simplePaginator = new SimplePaginator($page, Video::PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
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

    #[RequestMapping(methods: ['POST'], path: 'count')]
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
        if (env('APP_ENV') == 'develop') {
            $this->logger->info(json_encode($data));
        }
        $video = $VideoService->storeVideo($data);
        $tagService->videoCorrespondTag($data, $video->id);
        $actorService->videoCorrespondActor($data, $video->id);
        return $this->success([$video]);
    }

    #[RequestMapping(methods: ['POST'], path: 'find')]
    public function find(RequestInterface $request, VideoService $service)
    {
        $id = $request->input('id', 0);
        $data['models'] = $service->find($id);
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'search')]
    public function search(VideoApiSearchRequest $request, VideoService $service)
    {
        $title = $request->input('title');
        $length = $request->input('length', 0);
        $compare = $request->input('compare', 0);
        $page = (int) $request->input('page', 0);
        $limit = (int) $request->input('limit', Video::PAGE_PER);
        $sortBy = (int) $request->input('sort_by');
        $isAsc = (int) $request->input('is_asc');
        $data = [];
        $data['models'] = $service->searchVideo($title, $compare, $length, $page, $limit, $sortBy, $isAsc);
        $path = '/api/video/search';
        $simplePaginator = new SimplePaginator($page, $limit, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'suggest')]
    public function suggest(RequestInterface $request, VideoService $service, SuggestService $suggestService)
    {
        $page = (int) $request->input('page', 0);
        $userId = (int) auth()->user()->getId();
        $suggest = $suggestService->getTagProportionByMemberTag($userId);
        $models = $service->getVideosBySuggest($suggest, $page, Video::PAGE_PER);
        $data = [];
        $data['models'] = $models;
        $path = '/api/video/suggest';
        $simplePaginator = new SimplePaginator($page, Constants::DEFAULT_PAGE_PER, $path);
        $data = array_merge($data, $simplePaginator->render());
        return $this->success($data);
    }

    #[RequestMapping(methods: ['POST'], path: 'click')]
    public function saveClick(RequestInterface $request, ClickService $service)
    {
        $id = (int) $request->input('id');
        $service->addClick(Video::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['POST'], path: 'click/popular')]
    public function getClickPopular(ClickService $service)
    {
        $result = $service->getPopularClick(Video::class);

        return $this->success($result);
    }

    #[RequestMapping(methods: ['POST'], path: 'like')]
    public function saveLike(RequestInterface $request, LikeService $service)
    {
        $id = (int) $request->input('id');
        $service->addLike(Video::class, $id);
        return $this->success([]);
    }

    #[RequestMapping(methods: ['POST'], path: 'pay_video')]
    public function getPayVideo(RequestInterface $request, VideoService $service, GenerateService $generateService)
    {
        $id = (int) $request->input('id');
        $memberId = auth()->user()->getId();

        $product = Product::where('expire', Product::EXPIRE['no'])
            ->where('type', Video::class)
            ->where('correspond_id', $id)
            ->first();

        $isPay = $service->isPay($id, $memberId);
        if (empty($product) and ! $isPay) {
            return $this->error(trans('validation.product_is_expire'), 400);
        }

        if (! $isPay) {
            return $this->success([
                'url' => '',
                'product_id' => $product->id,
            ]);
        }

        $model = $service->getPayVideo($id)->toArray();
        $url = env('VIDEO_SOURCE_URL', 'https://video.iwanna.tv') . $model['m3u8'];

        return $this->success([
            'url' => $url,
            'product_id' => $product->id,
        ]);
    }

    #[RequestMapping(methods: ['POST'], path: 'pay_method')]
    public function getPayMethod(RequestInterface $request)
    {
        $id = (int) $request->input('id');
        $memberId = auth()->user()->getId();

        $product = Product::where('type', Video::class)
            ->where('correspond_id', $id)
            ->first();

        $price = null;

        $video = Video::find($id);

        if ($video->is_free == Video::VIDEO_TYPE['diamond']) {
            $price = (string) $product->selling_price;
        }

        $member = Member::find($memberId);

        return $this->success([
            'diamond' => [
                'price' => "1",
                'member_diamond_coin' => (string) $member->diamond_coins,
            ],
            'coin' => [
                'price' => $price,
                'member_coin' => (string) $member->coins,
            ]
        ]);
    }
}
