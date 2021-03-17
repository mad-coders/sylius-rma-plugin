<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Generator\ReturnNumberGenerator;
use Sylius\Component\Core\Model\OrderInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Exception;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnRequestBuilder
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var RepositoryInterface */
    private $orderReturnRepository;

    /** @var ReturnNumberGenerator */
    private $orderReturnGenerator;

    /**
     * ReturnRequestBuilder constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param RepositoryInterface $orderReturnRepository
     * @param ReturnNumberGenerator $orderReturnGenerator
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RepositoryInterface $orderReturnRepository,
        ReturnNumberGenerator $orderReturnGenerator
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->orderReturnGenerator = $orderReturnGenerator;
    }

    /**
     * @param string $orderNumber
     * @return OrderReturnInterface
     * @throws Exception
     */
    public function build(string $orderNumber): OrderReturnInterface
    {
        $order = $this->orderRepository->findOneByNumber($orderNumber);

        if (!$order instanceof OrderInterface) {
            throw new Exception(sprintf('$order must implement %s interface', OrderInterface::class));
        }

        $c = [ 'orderNumber' => $orderNumber, 'orderReturnStatus' => OrderReturnInterface::STATUS_DRAFT ];
        $orderReturn = $this->orderReturnRepository->findOneBy($c);

        // if draft order already exists then return it
        // ONLY ONE draft order return object per sales order is allowed
        if ($orderReturn instanceof OrderReturnInterface) {
            return $orderReturn;
        }

        $orderReturn = new OrderReturn();

        // populate order data
        $orderReturnNumber = $this->orderReturnGenerator->returnNumberGenerate($orderNumber);
        $orderReturn->setReturnNumber($orderReturnNumber);
        $orderReturn->setChannelCode($order->getChannel()->getCode());
        $orderReturn->setOrderNumber($order->getNumber());

        // check if customer exists
        if (!$customer = $order->getCustomer()) {
            throw new Exception('Customer is missing');
        }

        // populate customer email
        $orderReturn->setCustomerEmail($customer->getEmail());

        // check if address exists
        if (!$address = $order->getBillingAddress()) {
            throw new Exception('Customer address is missing');
        }

        // populate address
        $orderReturn->setFirstName($address->getFirstName());
        $orderReturn->setLastName($address->getLastName());
        $orderReturn->setStreet($address->getStreet());
        $orderReturn->setPostcode($address->getPostcode());
        $orderReturn->setCity($address->getCity());
        $orderReturn->setCountryCode($address->getCountryCode());
        $orderReturn->setPhoneNumber($address->getPhoneNumber());
        $orderReturn->setCompany($address->getCompany());

        foreach ($order->getItems() as $item) {
            $orderItemVariant = $item->getVariant();

            if (!$orderItemVariant instanceof ProductVariantInterface) {
                throw new \Exception(sprintf('$item->getVariant() must return %s', ProductVariantInterface::class));
            }

            if (!$orderItemVariant->getCode()) {
                throw new \Exception('Cannot create OrderItemReturnRequest for OrderItem without code.');
            }

            // TODO: $maxQty should be calculated based on following pattern: $qtyOrdered(orShipped) - $qtyAlreadyReturned
            $maxQty = $item->getQuantity();

            $orderReturnItem= new OrderReturnItem();
            $orderReturnItem->setUnitPrice($item->getUnitPrice());
            $orderReturnItem->setProductName($item->getProductName());
            $orderReturnItem->setProductSku($orderItemVariant->getCode());
            $orderReturnItem->setMaxQty($maxQty);
            $orderReturnItem->setReturnQty($maxQty);

            $orderReturn->addItem($orderReturnItem);
        }

        $this->orderReturnRepository->add($orderReturn);

        return $orderReturn;
    }
}
