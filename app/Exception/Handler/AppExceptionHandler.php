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
namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    protected \Hyperf\HttpServer\Contract\ResponseInterface $response;

    protected \Psr\Log\LoggerInterface $loggerFactory;

    public function __construct(protected StdoutLoggerInterface $logger, \Hyperf\HttpServer\Contract\ResponseInterface $response, LoggerFactory $loggerFactory)
    {
        $this->response = $response;
        $this->loggerFactory = $loggerFactory->get('error', 'error');
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());
        $this->loggerFactory->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->loggerFactory->error($throwable->getTraceAsString());
        return $this->response->redirect('/');
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
