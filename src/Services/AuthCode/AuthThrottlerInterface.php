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

/**
 * Rate limits the auth-code endpoints (code request and verification) to make brute forcing the
 * emailed code infeasible. Throttling is gated by a feature flag so integrators that already front
 * the shop with their own limiter can turn it off (see security issue #26).
 */
interface AuthThrottlerInterface
{
    /**
     * Records one hit against the given key and reports whether the caller is now rate limited.
     *
     * @return int|null the number of seconds to wait before retrying (for a Retry-After header) when
     *                  the limit is exceeded, or null when the request is allowed / throttling is off
     */
    public function throttle(string $key): ?int;

    /**
     * Clears the counter for a key, e.g. after a successful verification so a legitimate customer is
     * not left throttled by their earlier failed attempts.
     */
    public function reset(string $key): void;
}
