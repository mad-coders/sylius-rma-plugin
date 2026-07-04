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

use Madcoders\SyliusRmaPlugin\Services\AuthCode\AuthCodeSecretGenerator;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class AuthCodeSecretGeneratorTest extends UnitTestCase
{
    /** @test */
    function it_generates_a_code_within_the_configured_range(): void
    {
        $generator = new AuthCodeSecretGenerator(1000, 2000);

        for ($i = 0; $i < 200; ++$i) {
            $code = $generator->generate();
            $this->assertGreaterThanOrEqual(1000, $code);
            $this->assertLessThanOrEqual(2000, $code);
        }
    }

    /** @test */
    function it_defaults_to_an_eight_digit_code(): void
    {
        $generator = new AuthCodeSecretGenerator();

        for ($i = 0; $i < 200; ++$i) {
            $this->assertGreaterThanOrEqual(10000000, $generator->generate());
            $this->assertLessThanOrEqual(99999999, $generator->generate());
        }
    }

    /** @test */
    function it_does_not_always_return_the_same_value(): void
    {
        // guards against a constant/broken generator; the odds of 50 identical CSPRNG draws are nil
        $generator = new AuthCodeSecretGenerator();

        $values = [];
        for ($i = 0; $i < 50; ++$i) {
            $values[$generator->generate()] = true;
        }

        $this->assertGreaterThan(1, count($values));
    }
}
