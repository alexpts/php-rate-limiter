<?php

use PHPUnit\Framework\TestCase;
use PTS\RateLimiter\Adapter\MemoryAdapter;
use PTS\RateLimiter\Limiter;

class LimiterTest extends TestCase
{
    /** @var Limiter */
    protected $limiter;
    protected $testKey = 'test_limiter';

    public function setUp()
    {
        parent::setUp();

        $adapter = new MemoryAdapter;
        $this->limiter = new Limiter($adapter);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->limiter->reset($this->testKey);
    }

    public function testGet(): void
    {
        $limiter = $this->limiter;
        $value = $limiter->get('key1');

        static::assertSame(0, $value);
    }

    public function testGet2(): void
    {
        $limiter = $this->limiter;
        $limiter->inc($this->testKey);
        $limiter->inc($this->testKey);

        static::assertSame(2, $limiter->get($this->testKey));
    }

    public function testInc(): void
    {
        $limiter = $this->limiter;
        $value = $limiter->inc($this->testKey);

        static::assertSame(1, $value);
    }

    public function testReset(): void
    {
        $limiter = $this->limiter;
        $limiter->inc($this->testKey);
        $limiter->reset($this->testKey);

        static::assertSame(0, $limiter->get($this->testKey));
    }

    public function testIsExceeded(): void
    {
        $limiter = $this->limiter;

        $limiter->inc($this->testKey);
        $limiter->inc($this->testKey);

        $isMax1 = $limiter->isExceeded($this->testKey, 1);
        $isMax2 = $limiter->isExceeded($this->testKey, 10);

        static::assertTrue($isMax1);
        static::assertFalse($isMax2);
    }
}
