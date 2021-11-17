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
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class OrderContext implements Context
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var FactoryInterface */
    private $shipmentFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        FactoryInterface $shipmentFactory
    )
    {
        $this->orderRepository = $orderRepository;
        $this->shipmentFactory = $shipmentFactory;
    }

    /**
     * @Given /^(the order)'s state is "([^"]+)"/
     */
    public function setOrderState(OrderInterface $order, string $state): void
    {
        $order->setState($state);
        $this->orderRepository->add($order);
    }

    /**
     * @Given /^(the order) has single shipment with ("[^"]+" shipping method)/
     */
    public function addSingleShipment(OrderInterface $order, ShippingMethodInterface $shippingMethod): void
    {
        /** @var ShipmentInterface $shipment */
        $shipment = $this->shipmentFactory->createNew();
        $shipment->setState(ShipmentInterface::STATE_READY);
        $shipment->setMethod($shippingMethod);

        $order->addShipment($shipment);
        $order->setShippingState('ready');
        $this->orderRepository->add($order);
    }
}
