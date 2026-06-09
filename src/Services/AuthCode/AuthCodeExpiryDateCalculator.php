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

use DateInterval;
use DateTime;
use Exception;

final readonly class AuthCodeExpiryDateCalculator implements AuthCodeExpiryDateCalculatorInterface
{
    private const DEFAULT_TIME_INTERVAL = 'PT5M';

    public function __construct(private string $timeInterval = self::DEFAULT_TIME_INTERVAL)
    {
    }

    /**
     * @throws Exception
     */
    public function calculate(): DateTime
    {
        $expiresAtDate = new DateTime();
        $dateInterval = new DateInterval($this->timeInterval);
        $expiresAtDate->add($dateInterval);

        return $expiresAtDate;
    }
}
