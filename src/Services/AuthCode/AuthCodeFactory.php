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

use InvalidArgumentException;
use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final readonly class AuthCodeFactory implements AuthCodeFactoryInterface
{
    /**
     * @param RepositoryInterface<AuthCodeInterface> $authCodeRepository
     */
    public function __construct(
        private AuthCodeHashGeneratorInterface $authCodeHashGenerator,
        private AuthCodeSecretGeneratorInterface $authCodeSecretGenerator,
        private RepositoryInterface $authCodeRepository,
        private AuthCodeExpiryDateCalculatorInterface $authCodeExpiryDate,
    ) {
    }

    public function createForOrder(OrderInterface $order): AuthCodeInterface
    {
        if (!is_string($order->getNumber())) {
            $orderId = $order->getId();

            throw new InvalidArgumentException(sprintf(
                'Order id: "%s", has not order number defined',
                is_scalar($orderId) ? (string) $orderId : '',
            ));
        }

        $authCodeSecret = $this->authCodeSecretGenerator->generate();
        $hash = $this->authCodeHashGenerator->generateForOrder($order);

        $authCode = new AuthCode();
        $authCode->setOrderNumber($order->getNumber());
        $authCode->setAuthCode($authCodeSecret);
        $authCode->setHash($hash);
        $authCode->setExpiresAt($this->authCodeExpiryDate->calculate());

        $this->authCodeRepository->add($authCode);

        return $authCode;
    }
}
