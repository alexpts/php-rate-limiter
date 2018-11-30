# Rate Limiter

[![SymfonyInsight](https://insight.symfony.com/projects/20e239e7-e00e-46a0-b328-a2a31864b841/big.svg)](https://insight.symfony.com/projects/20e239e7-e00e-46a0-b328-a2a31864b841)
[![Build Status](https://travis-ci.org/alexpts/php-rate-limiter.svg?branch=master)](https://travis-ci.org/alexpts/php-rate-limiter)
[![Code Coverage](https://scrutinizer-ci.com/g/alexpts/php-rate-limiter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/alexpts/php-rate-limiter/?branch=master)
[![Code Climate](https://codeclimate.com/github/alexpts/php-rate-limiter/badges/gpa.svg)](https://codeclimate.com/github/alexpts/php-rate-limiter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alexpts/php-rate-limiter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alexpts/php-rate-limiter/?branch=master)


Rate limiter + PSR-15 middleware


#### Install

`composer require alexpts/php-rate-limiter`


#### Example

```php
<?php

use PTS\NextRouter\Router;
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

$psr15Runner = new Router(); // relay or other psr-15 runner
$psr15Runner->getStore()->middleware($limiterMiddleware);

$psr7Request = ServerRequestFactory::fromGlobals();
$response = $psr15Runner->handle($psr7Request);

// flush response or other
// ...

```
