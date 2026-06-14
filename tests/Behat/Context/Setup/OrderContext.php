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
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class OrderContext implements Context
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var FactoryInterface */
    private $shipmentFactory;

    /** @var OrderItemQuantityModifierInterface */
    private $orderItemQuantityModifier;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        FactoryInterface $shipmentFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
    ) {
        $this->orderRepository = $orderRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
    }

    /**
     * @Given /^(the order) contains (\d+) units of "([^"]+)"$/
     */
    public function theOrderContainsUnitsOf(OrderInterface $order, int $quantity, string $productName): void
    {
        foreach ($order->getItems() as $item) {
            if ($item->getProductName() === $productName) {
                $this->orderItemQuantityModifier->modify($item, $quantity);
                $this->orderRepository->add($order);

                return;
            }
        }

        // fall back to the first line item when the product-name snapshot differs
        $firstItem = $order->getItems()->first();
        if ($firstItem instanceof OrderItemInterface) {
            $this->orderItemQuantityModifier->modify($firstItem, $quantity);
            $this->orderRepository->add($order);
        }
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
     * @Given /^(the order)'s payment state is "([^"]+)"/
     */
    public function setOrderPaymentState(OrderInterface $order, string $paymentState): void
    {
        $order->setPaymentState($paymentState);
        $this->orderRepository->add($order);
    }

    /**
     * @Given /^(the order)'s shipping state is "([^"]+)"/
     */
    public function setOrderShippingState(OrderInterface $order, string $shippingState): void
    {
        $order->setShippingState($shippingState);
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
