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

use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Component\Order\Model\OrderInterface;
use InvalidArgumentException;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AuthCodeFactory implements AuthCodeFactoryInterface
{
    /** @var AuthCodeHashGeneratorInterface */
    private $authCodeHashGenerator;

    /** @var AuthCodeSecretGeneratorInterface */
    private $authCodeSecretGenerator;

    /** @var RepositoryInterface */
    private $authCodeRepository;

    /** @var AuthCodeExpiryDateCalculatorInterface */
    private $authCodeExpiryDate;

    public function __construct(
        AuthCodeHashGeneratorInterface        $authCodeHashGenerator,
        AuthCodeSecretGeneratorInterface      $authCodeSecretGenerator,
        RepositoryInterface                   $authCodeRepository,
        AuthCodeExpiryDateCalculatorInterface $authCodeExpiryDate
    )
    {
        $this->authCodeHashGenerator = $authCodeHashGenerator;
        $this->authCodeSecretGenerator = $authCodeSecretGenerator;
        $this->authCodeRepository = $authCodeRepository;
        $this->authCodeExpiryDate = $authCodeExpiryDate;
    }

    public function createForOrder(OrderInterface $order): AuthCodeInterface
    {
        if (!is_string($order->getNumber())) {
            throw new InvalidArgumentException(sprintf(
                'Order id: "%s", has not order number defined',
                (string)$order->getId()
            ));
        }

        $authCodeSecret = $this->authCodeSecretGenerator->generate();
        $hash = $this->authCodeHashGenerator->generateForOrder($order);

        $authCode = new AuthCode();
        $authCode->setOrderNumber((string)$order->getNumber());
        $authCode->setAuthCode($authCodeSecret);
        $authCode->setHash($hash);
        $authCode->setExpiresAt($this->authCodeExpiryDate->calculate());

        $this->authCodeRepository->add($authCode);

        return $authCode;
    }
}
