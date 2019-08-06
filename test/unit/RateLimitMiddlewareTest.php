<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use PTS\NextRouter\Next;
use PTS\RateLimiter\Adapter\MemoryAdapter;
use PTS\RateLimiter\Limiter;
use PTS\RateLimiter\RateLimitMiddleware;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;

class RateLimitMiddlewareTest extends TestCase
{
    public function testLimiter(): void
    {
        $store = new MemoryAdapter;
        $limiter = new Limiter($store);
        $response = new JsonResponse(['error' => 'Too Many Requests']);
        $md = new RateLimitMiddleware($limiter, $response);
        $md->setMax(2);

        $app = $this->getApp($md);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('client-ip', '127.0.0.1');

        $response1 = $app->handle($request);
        $response2 = $app->handle($request);
        $response3 = $app->handle($request);

        static::assertSame(200, $response1->getStatusCode());
        static::assertSame(200, $response2->getStatusCode());
        static::assertSame(429, $response3->getStatusCode());
    }

    public function testSetKeyAttr(): void
    {
        $store = new MemoryAdapter;
        $limiter = new Limiter($store);
        $response = new JsonResponse(['error' => 'Too Many Requests']);

        $md = new RateLimitMiddleware($limiter, $response);
        $md->setKeyAttr('pid');

        $app = $this->getApp($md);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('pid', 'some');

        $app->handle($request);
        $app->handle($request);

        $value = $limiter->get('limiter.some');
        static::assertSame(2, $value);
    }

    public function testSetKeyPrefix(): void
    {
        $store = new MemoryAdapter;
        $limiter = new Limiter($store);
        $response = new JsonResponse(['error' => 'Too Many Requests']);

        $md = new RateLimitMiddleware($limiter, $response);
        $md->setKeyAttr('pid');
        $md->setKeyPrefix('api.rate');

        $app = $this->getApp($md);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('pid', 'some');

        $app->handle($request);
        $app->handle($request);

        $value = $limiter->get('api.rate.some');
        static::assertSame(2, $value);
    }

    public function testTtl(): void
    {
        $store = new MemoryAdapter;
        $limiter = new Limiter($store);
        $response = new JsonResponse(['error' => 'Too Many Requests']);

        $md = new RateLimitMiddleware($limiter, $response);
        $md->setKeyAttr('pid');
        $md->setTtl(20);

        $app = $this->getApp($md);
        $request = ServerRequestFactory::fromGlobals();
        $request = $request->withAttribute('pid', 'some');

        $app->handle($request);
        $app->handle($request);

        $value = $limiter->ttl('limiter.some');
        static::assertSame(20, $value);
    }

    protected function getApp(MiddlewareInterface $md): Next
    {
        $app = new Next;
        $app->getStoreLayers()
            ->middleware($md)
            ->use(function ($request, $next) {
                return new JsonResponse(['status' => 200]);
            });

        return $app;
    }
}
