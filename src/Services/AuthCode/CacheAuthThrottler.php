<?php

/*
 * This file is part of package:
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services\AuthCode;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * Fixed-window rate limiter backed by a PSR-6 cache pool. A dedicated in-plugin limiter (rather than
 * the Symfony RateLimiter component) keeps the plugin self-contained: it needs no extra Composer
 * dependency and no host-level framework.rate_limiter configuration - only a cache pool, which every
 * Symfony application already provides.
 */
final readonly class CacheAuthThrottler implements AuthThrottlerInterface
{
    private const CACHE_KEY_PREFIX = 'madcoders_rma_auth_throttle_';

    public function __construct(
        private CacheItemPoolInterface $cache,
        private bool $enabled,
        private int $limit = 5,
        private int $intervalSeconds = 300,
    ) {
    }

    public function throttle(string $key): ?int
    {
        if (!$this->enabled) {
            return null;
        }

        $now = time();

        try {
            $item = $this->cache->getItem($this->cacheKey($key));
        } catch (InvalidArgumentException) {
            // A malformed key must never be able to disable throttling: fail closed.
            return $this->intervalSeconds;
        }

        $count = 0;
        $resetAt = $now + $this->intervalSeconds;

        $window = $item->get();
        if (
            is_array($window) &&
            isset($window['count'], $window['reset_at']) &&
            is_int($window['count']) &&
            is_int($window['reset_at']) &&
            $window['reset_at'] > $now
        ) {
            $count = $window['count'];
            $resetAt = $window['reset_at'];
        }

        if ($count >= $this->limit) {
            return max(1, $resetAt - $now);
        }

        $item->set(['count' => $count + 1, 'reset_at' => $resetAt]);
        $item->expiresAfter(max(1, $resetAt - $now));
        $this->cache->save($item);

        return null;
    }

    public function reset(string $key): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $this->cache->deleteItem($this->cacheKey($key));
        } catch (InvalidArgumentException) {
            // Nothing to clear if the key is unusable.
        }
    }

    private function cacheKey(string $key): string
    {
        return self::CACHE_KEY_PREFIX . sha1($key);
    }
}
