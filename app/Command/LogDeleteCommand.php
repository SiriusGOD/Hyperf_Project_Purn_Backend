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

use App\Service\ObfuscationService;
use Carbon\Carbon;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
#[Command]
class LogDeleteCommand extends HyperfCommand
{
    public const OLD_DAY = 7;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('log:delete');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('刪除過舊 log');
    }

    public function handle()
    {
        $path = BASE_PATH . '/runtime/logs/';
        $files = scandir($path);

        $oldBaseDay = Carbon::now();
        foreach ($files as $file) {
            $baseName = basename($file, '.log');
            $nameArr = explode('-', $baseName);
            $logName = $nameArr[0];
            unset($nameArr[0]);
            $fileDateString = implode('-', $nameArr);
            $this->info('file date ' . $fileDateString);
            $fileDate = Carbon::parse($fileDateString);
            if ($fileDate->diffInDays($oldBaseDay) >= self::OLD_DAY) {
                $name = $logName . '-' . $fileDateString . '.log';
                $this->info('delete ' . $name);
                unlink($path . $logName . '-' . $fileDateString . '.log');
            }
        }
    }
}
