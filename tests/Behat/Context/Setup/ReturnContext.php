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
use Behat\Gherkin\Node\TableNode;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReason;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnContext implements Context
{
    /** @var RepositoryInterface */
    private $returnRepository;

    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var RepositoryInterface */
    private $returnReasonRepository;

    /**
     * ReturnContext constructor
     *
     * @param RepositoryInterface $returnRepository
     * @param SharedStorageInterface $sharedStorage
     * @param RepositoryInterface $returnReasonRepository
     */
    public function __construct(
        RepositoryInterface $returnRepository,
        SharedStorageInterface $sharedStorage,
        RepositoryInterface $returnReasonRepository
    )
    {
        $this->returnRepository = $returnRepository;
        $this->sharedStorage = $sharedStorage;
        $this->returnReasonRepository = $returnReasonRepository;
    }

    /**
     * @Given /^I have order return with number "([^"]+)" and status "([^"]+)" for (latest order)$/
     */
    public function iHaveOrderReturnWithStatus(string $orderReturnNumber, string $orderReturnStatus, OrderInterface $order): void
    {
        $orderReturn = new OrderReturn();
        $orderReturn->setReturnNumber($orderReturnNumber);
        $orderReturn->setOrderReturnStatus($orderReturnStatus);
        $orderReturn->setOrderNumber($order->getNumber());
        $orderReturn->setCustomerNumber((string)$order->getCustomer()->getId());
        $orderReturn->setCustomerEmail($order->getCustomer()->getEmail());
        $orderReturn->setChannelCode($order->getChannel()->getCode());
        $orderReturn->setReturnReason($this->getReturnReasonCode());

        $this->returnRepository->add($orderReturn);
    }

    private function getReturnReasonCode(): string
    {
        $reason = $this->returnReasonRepository->findAll();
        if (count($reason) > 0) {
            return $reason[0]->getCode();
        }
        return '';
    }
}
