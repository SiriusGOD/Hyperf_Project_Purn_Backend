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
namespace App\Command;

use App\Model\Video;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Intervention\Image\ImageManager;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class CountJsonCommand extends HyperfCommand
{
    public const OLD_DAY = 7;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('json:count');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('計算 json models 個數');
    }

    public function handle()
    {
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJqd3QifQ.eyJzdWIiOiIxIiwiaXNzIjoiaHR0cDpcL1wvOiIsImV4cCI6MTY4NTA4NDM0NiwiaWF0IjoxNjg0OTk3OTQ2LCJuYmYiOjE2ODQ5OTc5NDYsInVpZCI6MjQsInMiOiJpTUFRYWoiLCJqdGkiOiJhYmZmZDZmNmEzYmM0YmFiNzkyYTU4MmYzYjk0MTZmMiJ9.NjkxZGYyYjBmYTAxNTc4MWNiYjFhOGExMzFjOTExMWZmOTgwMmU1ZQ',
            'Cookie' => 'HYPERF_SESSION_ID=yabq4CdDzYtgxKMVUskStTb8jsqLW7jpfGVOvJBZ'
        ];
        $body = '{
  "type": "video",
  "id": 1,
  "type_id": 684,
  "nav_id": 18,
  "limit" : 20
}';
        $request = new Request('POST', '172.104.46.27/api/navigation/detail', $headers, $body);
        $res = $client->send($request);
        $result = json_decode($res->getBody()->getContents(), true);
        $videoCount = 0;
        $imageGroupCount = 0;
        foreach ($result['data']['models'] as $model) {
            if ($model['model_type'] == 'video') {
                $videoCount++;
            }

            if($model['model_type'] == 'image_group') {
                $imageGroupCount++;
            }
        }
        $this->info('image group models count : ' . $imageGroupCount);
        $this->info('video models count : ' . $videoCount);
    }

    protected function getArguments()
    {
        return [];
    }
}
