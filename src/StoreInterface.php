<?php
declare(strict_types=1);

namespace PTS\RateLimiter;

interface StoreInterface
{
    public function get(string $key): int;

    /**
     * @param string $key
     * @param int $ttl
     *
     * @return int - new value
     */
    public function inc(string $key, int $ttl = 60): int;

    public function reset(string $key): bool;

    public function isExceeded(string $key, int $max): bool;

    public function ttl(string $key): ?int;
}
