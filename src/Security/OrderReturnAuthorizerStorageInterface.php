<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Security;


use Madcoders\SyliusRmaPlugin\Security\Exception\NotExistsException;

/**
 * Sylius RMA Plugin by MADCODERS
 *
 * @copyright MADCODERS (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE file
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */
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
