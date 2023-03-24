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
namespace App\Middleware;

use Hyperf\Context\Context;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class RequestIdMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container, LoggerFactory $loggerFactory)
    {
        $this->container = $container;
        $this->logger = $loggerFactory->get('log');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger->debug('include_files_count : ' . count(get_included_files()));
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Request-Id', md5((string) microtime(true)))
            ->withHeader('include_files_count', count(get_included_files()));
        Context::set(ResponseInterface::class, $response);
        return $handler->handle($request);
    }
}
