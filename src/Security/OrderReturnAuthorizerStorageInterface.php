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

namespace Madcoders\SyliusRmaPlugin\Security;

use Madcoders\SyliusRmaPlugin\Security\Exception\NotExistsException;

interface OrderReturnAuthorizerStorageInterface
{
    public const DEFAULT_EXPIRY_TIME = 3600;

    /**
     * @param string $orderNumber
     *
     * @return array
     *
     * @throws NotExistsException
     */
    public function get(string $orderNumber): array;

    /**
     * @param string $orderNumber
     * @param int $expiryTime
     */
    public function add(string $orderNumber, int $expiryTime = self::DEFAULT_EXPIRY_TIME): void;

    /**
     * @param string $orderNumber
     *
     * @return bool
     */
    public function exists(string $orderNumber): bool;
}
