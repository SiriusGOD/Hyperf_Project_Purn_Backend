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
use Hyperf\HttpServer\Contract\RequestInterface;
class CorsMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    /**
     * @var LoggerFactory
     */
    protected $loggerFactory;
    protected $request;
    protected $response;

    public function __construct(RequestInterface $request, ContainerInterface $container, LoggerFactory $loggerFactory, ResponseInterface $response)
    {
        $this->container = $container;
        $this->loggerFactory = $loggerFactory;
        $this->request = $request;
        $this->response = $response;
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

    public function handle($request, \Closure $next)
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        if ($dispatched->handler->callback === 'Hyperf\HttpServer\StaticServer::send') {
            $headers = [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            ];
            $this->response = $this->response->withHeaders($headers);
        }
        Context::set(ResponseInterface::class, $this->response);
        return $next($request);
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
}
