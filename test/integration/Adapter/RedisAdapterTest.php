<?php

use PHPUnit\Framework\TestCase;
use PTS\RateLimiter\Adapter\RedisAdapter;

class RedisAdapterTest extends TestCase
{
	/** @var RedisAdapter */
	protected $limiter;
	protected $testKey = 'test_limiter';

	public function setUp()
	{
		parent::setUp();

		$redis = new Redis;
		$redis->connect('127.0.0.1');
		$this->limiter = new RedisAdapter($redis);
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

	public function testReset(): void
	{
		$limiter = $this->limiter;
		$limiter->inc($this->testKey);
		$limiter->reset($this->testKey);

		static::assertSame(0, $limiter->get($this->testKey));
	}

	public function testTtl(): void
	{
		$limiter = $this->limiter;
		$limiter->inc($this->testKey);
		$ttl = $limiter->ttl($this->testKey);
		$ttl2 = $limiter->ttl($this->testKey . '2');

		static::assertSame(60, $ttl);
		static::assertNull($ttl2);
	}
}
