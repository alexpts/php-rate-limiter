<?php

use PHPUnit\Framework\TestCase;
use PTS\RateLimiter\Adapter\MemoryAdapter;

class MemoryAdapterTest extends TestCase
{
	/** @var MemoryAdapter */
	protected $limiter;
	protected $testKey = 'test_limiter';

	public function setUp()
	{
		parent::setUp();

		$this->limiter = new MemoryAdapter;
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

	public function testCleanExpired(): void
	{
		$limiter = $this->limiter = new MemoryAdapter(1);

		$limiter->inc($this->testKey, 1);
		$limiter->inc($this->testKey, 1);

		sleep(2);

		$value = $limiter->get($this->testKey);
		static::assertSame(0, $value);
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
