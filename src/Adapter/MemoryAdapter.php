<?php
declare(strict_types=1);

namespace PTS\RateLimiter\Adapter;

use PTS\RateLimiter\StoreInterface;

class MemoryAdapter implements StoreInterface
{
	protected $cleanExpired = 30;
	protected $lastClean = 0;

	protected $store = [];
	protected $ttlKeys = [];

	public function get(string $key): int
	{
		$this->isNeedCleanExpired() && $this->cleanExpired();
		return $this->store[$key] ?? 0;
	}

	public function __construct(int $cleanExpired = 30)
	{
		$this->cleanExpired = $cleanExpired;
		$this->lastClean = time();
	}

	public function inc(string $key, int $ttl = 60): int
	{
		$value = $this->get($key);
		$this->store[$key] = ++$value;

		if ($value === 1) {
			$this->ttlKeys[$key] = time() + $ttl;
		}

		return $value;
	}

	public function reset(string $key): bool
	{
		unset($this->store[$key], $this->ttlKeys[$key]);
		return true;
	}

	public function isExceeded(string $key, int $max): bool
	{
		$value = $this->get($key);
		return $value >= $max;
	}

	protected function isNeedCleanExpired(): bool
	{
		return ($this->lastClean + $this->cleanExpired) <= time();
	}

	protected function cleanExpired(): void
	{
		$current = time();

		$this->ttlKeys = array_filter($this->ttlKeys, function(int $time, string $key) use ($current) {
			$isExpired = $time < $current;
			if ($isExpired) {
				unset($this->store[$key]);
			}

			return !$isExpired;
		}, ARRAY_FILTER_USE_BOTH);
	}

	public function ttl(string $key): ?int
	{
		if (array_key_exists($key, $this->ttlKeys)) {
			$ttl = $this->ttlKeys[$key] - time();
			return $ttl > 0 ? $ttl : 0;
		}

		return null;
	}

}