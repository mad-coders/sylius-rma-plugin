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
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

class OrderContext implements Context
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var FactoryInterface */
    private $shipmentFactory;

    /** @var OrderItemQuantityModifierInterface */
    private $orderItemQuantityModifier;

    /** @var FactoryInterface */
    private $orderItemFactory;

    /** @var ProductVariantResolverInterface */
    private $variantResolver;

    /** @var SharedStorageInterface */
    private $sharedStorage;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        FactoryInterface $shipmentFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        FactoryInterface $orderItemFactory,
        ProductVariantResolverInterface $variantResolver,
        SharedStorageInterface $sharedStorage,
    ) {
        $this->orderRepository = $orderRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->orderItemFactory = $orderItemFactory;
        $this->variantResolver = $variantResolver;
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @Given /^(the order) also contains (\d+) units? of (product "[^"]+")$/
     */
    public function theOrderAlsoContainsUnitsOfProduct(OrderInterface $order, int $quantity, ProductInterface $product): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->sharedStorage->get('channel');

        /** @var ProductVariantInterface $variant */
        $variant = $this->variantResolver->getVariant($product);

        /** @var ChannelPricingInterface|null $channelPricing */
        $channelPricing = $variant->getChannelPricingForChannel($channel);

        /** @var OrderItemInterface $item */
        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);
        $item->setProductName($product->getName());
        $item->setUnitPrice(null !== $channelPricing ? (int) $channelPricing->getPrice() : 0);
        $this->orderItemQuantityModifier->modify($item, $quantity);

        $order->addItem($item);
        $this->orderRepository->add($order);
    }

    /**
     * @Given /^(the order)'s checkout state is "([^"]+)"$/
     */
    public function setOrderCheckoutState(OrderInterface $order, string $checkoutState): void
    {
        $order->setCheckoutState($checkoutState);
        $this->orderRepository->add($order);
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
