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
use Intervention\Image\ImageManager;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ImagickCommand extends HyperfCommand
{
    public const OLD_DAY = 7;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('imagick:run');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('測試壓縮圖片');
    }

    public function handle()
    {
        $path = $this->input->getArgument('path') ?? '';
        $this->info('path is : ' . $path);
        $manager = new ImageManager(['driver' => 'gd']);
        $manager = $manager->make($path);
        $manager->resize(intval($manager->width() * 0.5), intval($manager->height() * 0.5))->save('public/image/test.jpg', 50);
    }

    protected function getArguments()
    {
        return [
            ['path', InputArgument::REQUIRED, '路徑'],
        ];
    }
}
