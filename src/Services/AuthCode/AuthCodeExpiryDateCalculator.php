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

use DateTime;
use DateInterval;
use Exception;

final class AuthCodeExpiryDateCalculator implements AuthCodeExpiryDateCalculatorInterface
{
    /** @var string */
    private $timeInterval;

    private const DEFAULT_TIME_INTERVAL = 'PT5M';

    public function __construct(string $timeInterval = self::DEFAULT_TIME_INTERVAL)
    {
        $this->timeInterval = $timeInterval;
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
