<?php

use PTS\NextRouter\Next;
use PTS\RateLimiter\Adapter\MemoryAdapter;
use PTS\RateLimiter\Limiter;
use PTS\RateLimiter\RateLimitMiddleware;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;

require_once '../vendor/autoload.php';

$limitStore = new MemoryAdapter;
$rateLimiter = new Limiter($limitStore);
$response = new JsonResponse(['error' => 'Too Many Requests'], 429);

$limiterMiddleware = new RateLimitMiddleware($rateLimiter, $response);
$limiterMiddleware->setKeyAttr('ip');

$psr15Runner = new Next; // relay or other psr-15 runner
$psr15Runner->getStoreLayers()->middleware($limiterMiddleware);

$psr7Request = ServerRequestFactory::fromGlobals();
$response = $psr15Runner->handle($psr7Request);

// flush response or other
// ...
