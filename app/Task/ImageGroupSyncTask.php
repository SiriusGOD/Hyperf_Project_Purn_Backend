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
use App\Model\SystemParam;
use App\Model\Tag;
use App\Service\ImageGroupService;
use App\Service\TagService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;

#[Crontab(name: 'ImageGroupSyncTask', rule: '5 * * * *', callback: 'execute', memo: '圖片同步定時任務')]
class ImageGroupSyncTask
{
    public const ADMIN_ID = 1;

    public const SYNC_KEY = 'image_group_sync';

    protected ImageGroupService $service;

    protected $tagService;

    protected Redis $redis;

    protected string $syncUrl;

    private \Psr\Log\LoggerInterface $logger;

    public function __construct(ImageGroupService $service, LoggerFactory $loggerFactory, Redis $redis, TagService $tagService)
    {
        $this->logger = $loggerFactory->get('crontab', 'crontab');
        $this->service = $service;
        $this->redis = $redis;
        $this->syncUrl = env('IMAGE_GROUP_SYNC_URL');
        $this->tagService = $tagService;
    }

    public function execute()
    {
        $count = $this->getCount();
        $forever = true;
        $client = new \GuzzleHttp\Client();
        while ($forever) {
            $this->logger->info('取得套圖同步資料，筆數 : ' . $count);
            $url = $this->syncUrl . '&_n=' . $count;
            $res = $client->get($url);
            $result = json_decode($res->getBody()->getContents(), true);
            if (empty($result['data'])) {
                $this->logger->info('無資料');
                $forever = false;
            }
            $id = $this->createImageGroup($result['data']);
            $tagsIds = $this->getTags($result['data']['tags']);
            if (! empty($tagsIds)) {
                $this->tagService->createTagRelationshipArr(ImageGroup::class, $id, $tagsIds);
            }
            $this->createImages($result['data']['resources'], $id);
        }

        return '';
    }

    protected function getCount(): int
    {
        $count = 0;
        if ($this->redis->exists(self::SYNC_KEY)) {
            return (int) $this->redis->get(self::SYNC_KEY);
        }

        $model = SystemParam::firstOrCreate([
            'description' => self::SYNC_KEY,
            'param' => 1,
        ]);

        $count = $model->param;
        $this->redis->set(self::SYNC_KEY, $count);

        return $count;
    }

    protected function createImageGroup(array $params): int
    {
        $model = ImageGroup::where('sync_id', $params['id'])->first();
        if (! empty($model)) {
            return $model->id;
        }

        $model = new ImageGroup();
        $model->user_id = self::ADMIN_ID;
        $model->title = $params['title'];
        $model->thumbnail = $params['thumb'];
        $model->url = $params['thumb'];
        $model->description = $params['desc'];
        $model->sync_id = $params['id'];

        $model->save();

        return $model->id;
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
        $model = Image::where('sync_id', $image['id'])->first();

        if (! empty($model)) {
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
        $model->save();
    }
}
