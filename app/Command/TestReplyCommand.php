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
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * @Command
 */
#[Command]
class TestReplyCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    /**
     *
     * @Inject
     * @var ObfuscationService
     */
    protected ObfuscationService $obfuscationService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('test_reply:run');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('測試加解密是否正確');
    }

    public function handle()
    {
        echo json_encode($this->obfuscationService->replyData(['status' => 1]), JSON_PRETTY_PRINT);
    }
}
