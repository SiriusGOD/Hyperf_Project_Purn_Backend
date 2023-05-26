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
            'Authorization' => 'Bearer eyJ0eXAiOiJqd3QifQ.eyJzdWIiOiIxIiwiaXNzIjoiaHR0cDpcL1wvOiIsImV4cCI6MTY4NTE3MTQ0NSwiaWF0IjoxNjg1MDg1MDQ1LCJuYmYiOjE2ODUwODUwNDUsInVpZCI6NTcsInMiOiJkdHJvYkQiLCJqdGkiOiJlMDRjODJmYTQzM2MyOGE3OWUzMzUxYzI0ZjUxOGQ0OSJ9.ZGU4OGFkYTcwM2M2ZThlYWQwNmVhZTBiYzRhZjI0ZDBiYTkyYzQ2OQ',
            'Cookie' => 'HYPERF_SESSION_ID=qT1N5LrX72Fr1egzvvFh83KkqjYO5SO8gIpohc0B'
        ];
        $request = new Request('POST', '172.104.46.27/api/navigation/search?id=1&limit=20', $headers);
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
