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

final readonly class AuthCodeSecretGenerator implements AuthCodeSecretGeneratorInterface
{
    private const DEFAULT_MIN_CODE = 10000000;

    private const DEFAULT_MAX_CODE = 99999999;

    public function __construct(
        private int $min = self::DEFAULT_MIN_CODE,
        private int $max = self::DEFAULT_MAX_CODE,
    ) {
    }

    /**
     * Uses the cryptographically secure random_int() rather than mt_rand(): the emitted value is
     * the only secret gating the return/withdrawal flow, so it must not be predictable. The default
     * range is an 8-digit code to enlarge the keyspace (see security issue #26).
     *
     * @throws \Random\RandomException when no cryptographically secure source of randomness is available
     */
    public function generate(): int
    {
        return random_int($this->min, $this->max);
    }
}
