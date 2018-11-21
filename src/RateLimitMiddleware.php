<?php
declare(strict_types=1);

namespace PTS\RateLimiter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    /** @var Limiter */
    protected $limiter;
    /** @var int */
    protected $ttl = 60;
    /** @var int */
    protected $max = 60;
    /** @var string */
    protected $keyPrefix = 'limiter';
    /** @var ResponseInterface */
    protected $responseTooManyRequest = '';
    /** @var string */
    protected $keyAttr = 'client-ip';

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

    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    protected function getKey(ServerRequestInterface $request): string
    {
        $key = $request->getAttribute($this->keyAttr);
        return sprintf('%s.%s', $this->keyPrefix, $key);
    }
}
