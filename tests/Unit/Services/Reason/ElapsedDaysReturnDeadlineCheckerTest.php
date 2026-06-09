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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services\Reason;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Services\Reason\ElapsedDaysReturnDeadlineChecker;
use Prophecy\PhpUnit\ProphecyTrait;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class ElapsedDaysReturnDeadlineCheckerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    function it_allows_a_return_within_the_deadline()
    {
        $checker = new ElapsedDaysReturnDeadlineChecker();

        $this->assertTrue($checker->isWithinDeadline($this->reasonWithDeadline(14), $this->daysAgo(5)));
    }

    /** @test */
    function it_allows_a_return_on_the_exact_deadline_day()
    {
        $checker = new ElapsedDaysReturnDeadlineChecker();

        $this->assertTrue($checker->isWithinDeadline($this->reasonWithDeadline(14), $this->daysAgo(14)));
    }

    /** @test */
    function it_rejects_a_return_once_the_total_elapsed_days_exceed_the_deadline()
    {
        $checker = new ElapsedDaysReturnDeadlineChecker();

        $this->assertFalse($checker->isWithinDeadline($this->reasonWithDeadline(14), $this->daysAgo(40)));
    }

    /** @test */
    function it_counts_total_days_across_month_and_year_boundaries()
    {
        $checker = new ElapsedDaysReturnDeadlineChecker();

        // ~one year and a few days: the day-of-month component is small, total days are large
        $this->assertFalse($checker->isWithinDeadline($this->reasonWithDeadline(14), $this->daysAgo(368)));
    }

    /** @test */
    function it_rejects_a_return_when_the_reason_has_no_deadline()
    {
        $checker = new ElapsedDaysReturnDeadlineChecker();

        $this->assertFalse($checker->isWithinDeadline($this->reasonWithDeadline(null), $this->daysAgo(1)));
    }

    private function reasonWithDeadline(?int $deadlineToReturn): OrderReturnReasonInterface
    {
        $reason = $this->prophesize(OrderReturnReasonInterface::class);
        $reason->getDeadlineToReturn()->willReturn($deadlineToReturn);

        return $reason->reveal();
    }

    private function daysAgo(int $days): \DateTimeInterface
    {
        return (new \DateTimeImmutable())->modify(sprintf('-%d days', $days));
    }
}
