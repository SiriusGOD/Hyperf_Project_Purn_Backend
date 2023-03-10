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

use App\Model\Seed;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

require_once BASE_PATH . '/seed/BaseInterface.php';

/**
 * @Command
 */
#[Command]
class SeedRunCommand extends HyperfCommand
{
    public const BASE_FILENAME = 'Seed.php';

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('seed:run');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('資料庫預設資料生成，以執行過的不會再被執行');
        $this->addOption('refresh', null, InputOption::VALUE_NONE, '重跑');
        $this->addOption('base', null, InputOption::VALUE_NONE, '只 seed 基本參數');
        $this->addOption('class', null, InputOption::VALUE_REQUIRED, '執行特定 class', '');
        $this->addOption('rollback', null, InputOption::VALUE_NONE, '執行上一次rollback');
    }

    public function handle()
    {
        $className = $this->input->getOption('class');
        if (! empty($className)) {
            $this->info('run seed special class : ' . $className);
            $this->classRun($className);
            return;
        }

        $option = $this->input->getOption('refresh');
        if ($option == 'refresh') {
            $this->info('run seed refresh');
            $this->refresh();
        }

        $option = $this->input->getOption('rollback');
        if ($option == 'rollback') {
            $this->info('run seed rollback');
            $this->rollback();
        }

        $isBase = false;
        $option = $this->input->getOption('base');
        if ($option == 'base' or env('APP_ENV', 'production') == 'production' or env('APP_ENV', 'prod') == 'prod') {
            $this->info('set only run base');
            $isBase = true;
        }
        $this->info('run seed');
        $this->seed($isBase);
    }

    public function classRun(string $className)
    {
        require_once BASE_PATH . '/seed/' . $className . '.php';
        $class = new $className();
        $class->down();
        $class->up();
        $lastSeed = Seed::orderByDesc('id')->first();
        $lastSeedBatch = 0;

        if (! empty($lastSeed)) {
            $lastSeedBatch = $lastSeed->batch;
        }
        if (! Seed::where('seed', $className)->exists()) {
            $this->info('add record : ' . $className);
            $this->addRecord($className, $lastSeedBatch + 1);
        }
    }

    public function refresh()
    {
        $seeds = Seed::orderByDesc('id')->get();

        foreach ($seeds as $seed) {
            require_once BASE_PATH . '/seed/' . $seed->seed . '.php';
            $class = new $seed->seed();
            $class->down();
        }

        Seed::truncate();
    }

    public function seed(bool $isBase)
    {
        $files = scandir(BASE_PATH . '/seed');
        $lastSeed = Seed::orderByDesc('id')->first();
        $lastSeedBatch = 0;

        if (! empty($lastSeed)) {
            $lastSeedBatch = $lastSeed->batch;
        }

        foreach ($files as $file) {
            if (str_contains($file, self::BASE_FILENAME)) {
                require_once BASE_PATH . '/seed/' . $file;
                $className = substr($file, 0, strlen($file) - 4);
                $class = new $className();
                $this->info('get ' . $className);
                if ($isBase and ! $class->base()) {
                    $this->info('is not base ' . $className);
                    continue;
                }
                if (Seed::where('seed', $className)->exists()) {
                    $this->info('is exist ' . $className);
                    continue;
                }
                $this->warn('run ' . $className);
                $class->up();
                $this->addRecord($className, $lastSeedBatch + 1);
            }
        }
    }

    public function rollback()
    {
        $lastSeed = Seed::orderByDesc('id')->first();
        $lastSeedBatch = 0;

        if (! empty($lastSeed)) {
            $lastSeedBatch = $lastSeed->batch;
        }

        $seeds = Seed::where('batch', $lastSeedBatch)->get();

        foreach ($seeds as $seed) {
            require_once BASE_PATH . '/seed/' . $seed->seed . '.php';
            $this->info('run rollback ' . $seed->seed);
            $class = new $seed->seed();
            $class->down();
            $seed->delete();
        }
    }

    protected function addRecord(string $name, int $batch): void
    {
        $model = new Seed();
        $model->seed = $name;
        $model->batch = $batch;
        $model->save();
    }

    protected function getArguments()
    {
        return [
        ];
    }
}
