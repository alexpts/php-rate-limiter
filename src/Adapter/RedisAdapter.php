<?php
declare(strict_types=1);

namespace PTS\RateLimiter\Adapter;

use PTS\RateLimiter\StoreInterface;
use Redis;

class RedisAdapter implements StoreInterface
{
    protected Redis $redis;


    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key): int
    {
        return (int)$this->redis->get($key);
    }

    public function inc(string $key, int $ttl = 60): int
    {
        $value = $this->redis->incr($key);
        if ($value === 1) {
            $this->redis->expire($key, $ttl);
        }

        return $value;
    }

    public function reset(string $key): bool
    {
        return (bool)$this->redis->del($key);
    }

    public function isExceeded(string $key, int $max): bool
    {
        $value = $this->get($key);
        return $value >= $max;
    }

    public function ttl(string $key): ?int
    {
        $ttl = $this->redis->ttl($key);
        return $ttl > 0 ? $ttl : null;
    }
}
