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
namespace App\Task;

use App\Model\Image;
use App\Model\ImageGroup;
use App\Model\Product;
use App\Model\SystemParam;
use App\Model\Tag;
use App\Service\ImageGroupService;
use App\Service\ProductService;
use App\Service\TagService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

#[Crontab(name: 'ImageGroupSyncTask', rule: '5 * * * *', callback: 'execute', memo: '圖片同步定時任務')]
class ImageGroupSyncTask
{
    public const ADMIN_ID = 0;

    public const SYNC_KEY = 'image_group_sync';

    protected ImageGroupService $service;

    protected $tagService;

    protected Redis $redis;

    protected int $count = 0;

    protected string $syncUrl;

    private \Psr\Log\LoggerInterface $logger;
    private ProductService $productService;

    public function __construct(ImageGroupService $service, LoggerFactory $loggerFactory, Redis $redis, TagService $tagService, ProductService $productService)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
        $this->redis = $redis;
        $this->syncUrl = env('IMAGE_GROUP_SYNC_URL');
        $this->tagService = $tagService;
        $this->productService = $productService;
    }

    public function execute()
    {
        $systemParam = $this->getCount();
        $count = (int) $systemParam->param;
        $forever = true;
        $client = new \GuzzleHttp\Client();
        while ($forever) {
            $this->logger->info('取得套圖同步資料，筆數 : ' . $count);
            $url = $this->syncUrl . '&_n=' . $count;
            try {
                $res = $client->get($url);
            } catch (\Exception $exception) {
                $this->logger->info('套圖錯誤 id : ' . $count);
                $count++;
                continue;
            }
            $result = json_decode($res->getBody()->getContents(), true);
            if (empty($result['data'])) {
                $this->logger->info('無資料');
                $forever = false;
            }

            if (ImageGroup::where('sync_id', $result['data']['id'])->exists() or count($result['data']['resources']) < 8) {
                $count++;
                continue;
            }

            $id = $this->createImageGroup($result['data']);
            if (empty($id)) {
                $this->logger->info('讀不到封面 ： ' . $result['data']['id']);
                $count++;
                continue;
            }
            $this->createProductGroup([
                'id' => $id,
                'title' => $result['data']['title'],
            ]);
            $tagsIds = $this->getTags($result['data']['tags']);
            if (! empty($tagsIds)) {
                $this->tagService->createTagRelationshipArr(ImageGroup::class, $id, $tagsIds);
            }
            $this->createImages($result['data']['resources'], $id);
            $count++;
        }

        $systemParam->param = $count;
        $systemParam->save();
        $this->productService->updateCache();

        return '';
    }

    protected function getCount(): SystemParam
    {
        return SystemParam::firstOrCreate([
            'description' => self::SYNC_KEY,
        ], [
            'description' => self::SYNC_KEY,
            'param' => 1
        ]);
    }

    protected function createImageGroup(array $params): ?int
    {
        $url = env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live');
        $imageInfo = getimagesize($url . $params['thumb']);
        if ($imageInfo === false) {
            return null;
        }
        $model = new ImageGroup();
        $model->user_id = self::ADMIN_ID;
        $model->title = $params['title'];
        $model->thumbnail = $params['thumb'];
        $model->url = $params['thumb'];
        $model->description = $params['desc'];
        $model->sync_id = $params['id'];
        $model->height = $imageInfo[1];
        $model->weight = $imageInfo[0];

        $model->save();

        return $model->id;
    }

    protected function createProductGroup(array $params) : void
    {
        $data['id'] = null;
        $data['type'] = Product::TYPE_CORRESPOND_LIST['image'];
        $data['correspond_id'] = $params['id'];
        $data['name'] = $params['title'];
        $data['user_id'] = 1;
        $data['expire'] = 0;
        $data['start_time'] = date('Y-m-d H:i:s');
        $data['end_time'] = date('Y-m-d H:i:s', strtotime('+10 years'));
        $data['currency'] = 'COIN';
        $data['diamond_price'] = 1;
        $data['selling_price'] = 0;
        $this->productService->store($data);
    }

    protected function getTags(string $tagsStr): array
    {
        $tagArr = explode(',', $tagsStr);
        $tags = Tag::whereIn('name', $tagArr)->get();
        if (empty($tags)) {
            return [];
        }

        return $tags->pluck('id')->toArray();
    }

    protected function createImages(array $images, int $imageGroupId): void
    {
        foreach ($images as $image) {
            $this->createImage($image, $imageGroupId);
        }
    }

    protected function createImage(array $image, int $imageGroupId): void
    {
        $url = env('IMAGE_GROUP_DECRYPT_URL', 'https://imgpublic.ycomesc.live');
        $imageInfo = getimagesize($url . $image['img_url']);
        if ($imageInfo === false) {
            return;
        }
        $model = new Image();
        $model->user_id = self::ADMIN_ID;
        $model->title = '';
        $model->thumbnail = $image['img_url'];
        $model->url = $image['img_url'];
        $model->description = '';
        $model->group_id = $imageGroupId;
        $model->sync_id = $image['id'];
        $model->thumbnail_height = $imageInfo[1] ?? 0;
        $model->thumbnail_weight = $imageInfo[0] ?? 0;
        $model->height = $imageInfo[1] ?? 0;
        $model->weight = $imageInfo[0] ?? 0;
        $model->save();
    }
}
