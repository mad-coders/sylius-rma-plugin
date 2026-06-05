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
    private const DEFAULT_MIN_CODE = 100000;

    private const DEFAULT_MAX_CODE = 999999;

    public function __construct(private int $min = self::DEFAULT_MIN_CODE, private int $max = self::DEFAULT_MAX_CODE)
    {
    }

    public function generate(): int
    {
        return mt_rand($this->min, $this->max);
    }
}
