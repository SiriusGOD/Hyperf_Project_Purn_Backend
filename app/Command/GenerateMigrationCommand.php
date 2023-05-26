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
class GenerateMigrationCommand extends HyperfCommand
{
    public const OLD_DAY = 7;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('generate:migration');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('generate');
    }

    public function handle()
    {
        $path = BASE_PATH . '/migrations/';
        $files = scandir($path);

        foreach ($files as $file) {
            if (!str_contains($file, '.php')) {
                continue;
            }
            $nameArr = explode('_', basename($file, '.php'));
            $name = '';
            foreach ($nameArr as $word) {
                if (is_numeric($word)) {
                    continue;
                }
                $name .= ucfirst($word);
            }
            $content = file_get_contents($path . $file);
            $result = str_replace('return new class', 'class '.$name, $content);
            file_put_contents($path . $file, $result);
        }
    }

    protected function getArguments()
    {
        return [];
    }
}
