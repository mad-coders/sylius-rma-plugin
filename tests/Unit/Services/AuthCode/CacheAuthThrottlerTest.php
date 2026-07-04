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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services\AuthCode;

use Madcoders\SyliusRmaPlugin\Services\AuthCode\CacheAuthThrottler;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class CacheAuthThrottlerTest extends UnitTestCase
{
    /** @test */
    function it_allows_requests_up_to_the_limit_then_blocks_with_a_retry_after(): void
    {
        $throttler = new CacheAuthThrottler(new ArrayAdapter(), true, 3, 300);

        $this->assertNull($throttler->throttle('ip_order'));
        $this->assertNull($throttler->throttle('ip_order'));
        $this->assertNull($throttler->throttle('ip_order'));

        $retryAfter = $throttler->throttle('ip_order');
        $this->assertNotNull($retryAfter);
        $this->assertGreaterThan(0, $retryAfter);
        $this->assertLessThanOrEqual(300, $retryAfter);
    }

    /** @test */
    function it_counts_keys_independently(): void
    {
        $throttler = new CacheAuthThrottler(new ArrayAdapter(), true, 1, 300);

        $this->assertNull($throttler->throttle('key_a'));
        $this->assertNotNull($throttler->throttle('key_a'));

        // a different key is unaffected by key_a hitting its limit
        $this->assertNull($throttler->throttle('key_b'));
    }

    /** @test */
    function reset_clears_the_counter_for_a_key(): void
    {
        $throttler = new CacheAuthThrottler(new ArrayAdapter(), true, 1, 300);

        $this->assertNull($throttler->throttle('key'));
        $this->assertNotNull($throttler->throttle('key'));

        $throttler->reset('key');

        $this->assertNull($throttler->throttle('key'));
    }

    /** @test */
    function it_never_throttles_when_disabled(): void
    {
        $throttler = new CacheAuthThrottler(new ArrayAdapter(), false, 1, 300);

        for ($i = 0; $i < 10; ++$i) {
            $this->assertNull($throttler->throttle('key'));
        }
    }
}
