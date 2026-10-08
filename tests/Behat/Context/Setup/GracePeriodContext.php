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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * Grants and revokes per-order grace periods (#67) directly in the database, so shop scenarios
 * do not depend on the admin UI.
 */
class GracePeriodContext implements Context
{
    public function __construct(
        private RepositoryInterface $returnReasonRepository,
        private FactoryInterface $gracePeriodFactory,
        private RepositoryInterface $gracePeriodRepository,
    ) {
    }

    /**
     * @Given /^the return reason "([^"]+)" has a grace period of (\d+) days for (the order)$/
     */
    public function theReturnReasonHasAGracePeriodForTheOrder(string $reasonCode, int $days, OrderInterface $order): void
    {
        /** @var OrderReturnReasonGracePeriodInterface $gracePeriod */
        $gracePeriod = $this->gracePeriodFactory->createNew();
        $gracePeriod->setOrder($order);
        $gracePeriod->setReason($this->reason($reasonCode));
        $gracePeriod->setExtraDays($days);

        $this->gracePeriodRepository->add($gracePeriod);
    }

    /**
     * @Given /^the grace period of the return reason "([^"]+)" for (the order) has been revoked$/
     */
    public function theGracePeriodHasBeenRevoked(string $reasonCode, OrderInterface $order): void
    {
        $gracePeriod = $this->gracePeriodRepository->findOneBy(['order' => $order, 'reason' => $this->reason($reasonCode)]);
        Assert::isInstanceOf($gracePeriod, OrderReturnReasonGracePeriodInterface::class);

        $this->gracePeriodRepository->remove($gracePeriod);
    }

    private function reason(string $code): OrderReturnReasonInterface
    {
        $reason = $this->returnReasonRepository->findOneBy(['code' => $code]);
        Assert::isInstanceOf($reason, OrderReturnReasonInterface::class);

        return $reason;
    }
}
