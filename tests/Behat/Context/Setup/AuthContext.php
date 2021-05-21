<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Madcoders\SyliusRmaPlugin\Entity\AuthCode;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class AuthContext implements Context
{
    /** @var RepositoryInterface */
    private $authCodeRepository;

    public function __construct(RepositoryInterface $authCodeRepository)
    {
        $this->authCodeRepository = $authCodeRepository;
    }

    /**
     * @Given auth code :code for order :order
     */
    public function createAuthCodeForOrder(int $code, OrderInterface $order): void
    {
        $expiryDate = new \DateTime();
        $expiryDate->modify('+1 Hour');

        $authCode = new AuthCode();
        $authCode->setAuthCode($code);
        $authCode->setHash(hash('sha256', $order->getNumber().time()));
        $authCode->setOrderNumber($order->getNumber());
        $authCode->setExpiresAt($expiryDate);

        $this->authCodeRepository->add($authCode);
    }
}
