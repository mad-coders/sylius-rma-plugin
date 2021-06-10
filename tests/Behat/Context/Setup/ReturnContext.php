<?php

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

    /**
     * ReturnContext constructor
     *
     * @param RepositoryInterface $returnRepository
     * @param SharedStorageInterface $sharedStorage
     */
    public function __construct(
        RepositoryInterface $returnRepository,
        SharedStorageInterface $sharedStorage
    )
    {
        $this->returnRepository = $returnRepository;
        $this->sharedStorage = $sharedStorage;
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
        $orderReturn->setChannelCode($order->getLocaleCode());

        $this->returnRepository->add($orderReturn);
    }
}
