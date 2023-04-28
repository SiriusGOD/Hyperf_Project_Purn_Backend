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

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class OutputTagsCommand extends HyperfCommand
{
    public const OLD_DAY = 7;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('tag:output');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('輸出標籤');
    }

    public function handle()
    {
        $client = new \GuzzleHttp\Client();
        $count = 1;
        $syncUrl = env('IMAGE_GROUP_SYNC_URL');
        $csv = [];
        $forever = true;
        while ($forever) {
            $this->info('取得套圖同步資料，筆數 : ' . $count);
            $url = $syncUrl . '&_n=' . $count;
            $this->info('取得套圖同步資料，url : ' . $url);
            try {
                $res = $client->get($url);
            } catch (\Exception $exception) {
                $this->info('錯誤 id : ' . $count);
                $count++;
                continue;
            }

            $result = json_decode($res->getBody()->getContents(), true);
            if (empty($result['data']) or $count == 1000) {
                $this->info('無資料');
                $forever = false;
            }

            $tags = $result['data']['tags'];
            $tagsArr = explode(',', $tags);
            foreach ($tagsArr as $tag) {
                if (! in_array($tag, $csv)) {
                    $csv[] = $tag;
                }
            }
            $count++;
        }

        $file = fopen(BASE_PATH . '/tag.csv', 'w+');
        foreach ($csv as $tag) {
            fputcsv($file, [$tag]);
        }
    }

    protected function getArguments()
    {
    }
}
