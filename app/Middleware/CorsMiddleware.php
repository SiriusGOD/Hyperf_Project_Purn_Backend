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
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Logger\LoggerFactory;

class CorsMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;
    /**
     * @var LoggerFactory
     */
    protected $loggerFactory;

    public function __construct(ContainerInterface $container, LoggerFactory $loggerFactory)
    {
        $this->container = $container;
        $this->loggerFactory = $loggerFactory;
    }
    protected function logRequest(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $queryParams = $request->getQueryParams();
        $body = $request->getParsedBody();
        $ip = $request->getHeaderLine('X-Forwarded-For') ?: $request->getServerParams()['remote_addr'] ?? '-';

        $logger = $this->loggerFactory->get('cors');
        $logger->info(sprintf('%s %s %s %s %s', $method, $path, json_encode($queryParams), json_encode($body), $ip));
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 寫入跨域請求日誌
        $this->logRequest($request);
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Methods', '*')
            // Headers 可以根据实际情况进行改写。
            ->withHeader(
                'Access-Control-Allow-Headers',
                'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization,X-Token'
            );

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() === 'OPTIONS') {
            return $response;
        }

        return $handler->handle($request);
    }
}
