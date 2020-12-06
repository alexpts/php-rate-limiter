<?php
declare(strict_types=1);

namespace PTS\RateLimiter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    protected Limiter $limiter;
    protected int $ttl = 60; // sec
    protected int $max = 60; // sec
    protected string $keyPrefix = 'limiter';
    protected ResponseInterface $responseTooManyRequest;
    protected string $keyAttr = 'client-ip';

    public function __construct(Limiter $limiter, ResponseInterface $response)
    {
        $this->limiter = $limiter;
        $this->responseTooManyRequest = $response->withStatus(429, 'Too Many Requests');
    }

    public function setKeyAttr(string $attr): void
    {
        $this->keyAttr = $attr;
    }

    public function setKeyPrefix(string $keyPrefix): void
    {
        $this->keyPrefix = $keyPrefix;
    }

    public function setMax(int $max): void
    {
        $this->max = $max;
    }

    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $limiterKey = $this->getKey($request);

        if ($this->limiter->isExceeded($limiterKey, $this->max)) {
            return $this->responseTooManyRequest;
        }

        $response = $next->handle($request);
        $this->limiter->inc($limiterKey, $this->ttl);

        return $response;
    }

    protected function getKey(ServerRequestInterface $request): string
    {
        $key = $request->getAttribute($this->keyAttr);
        return sprintf('%s.%s', $this->keyPrefix, $key);
    }
}
