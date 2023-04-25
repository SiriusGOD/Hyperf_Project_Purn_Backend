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
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class TaskRunCommand extends HyperfCommand
{
    public const OLD_DAY = 7;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('task:run');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('跑特定 task');
    }

    public function handle()
    {
        $argument = $this->input->getArgument('class') ?? '';

        $className = 'App\\Task\\' . $argument;
        $this->line('class is ' . $className, 'info');

        $task = make($className);
        $task->execute();
    }

    protected function getArguments()
    {
        return [
            ['class', InputArgument::OPTIONAL, 'task class'],
        ];
    }
}
