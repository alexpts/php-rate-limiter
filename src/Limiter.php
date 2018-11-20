<?php
declare(strict_types=1);

namespace PTS\RateLimiter;

class Limiter
{
	/** @var StoreInterface */
	protected $store;

	public function __construct(StoreInterface $store)
	{
		$this->store = $store;
	}

	public function get(string $key): int
	{
		return $this->store->get($key);
	}

	public function inc(string $key, int $ttl = 60): int
	{
		return $this->store->inc($key, $ttl);
	}

	public function reset(string $key): bool
	{
		return $this->store->reset($key);
	}

	public function isExceeded(string $key, int $max): bool
	{
		return $this->store->isExceeded($key, $max);
	}

	public function ttl(string $key): ?int
	{
		return $this->store->ttl($key);
	}
}
