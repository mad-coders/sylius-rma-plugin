<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
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

    /** @var MaxQtyCalculator */
    private $maxQtyCalculator;

    /** @var RmaChangesLogger */
    private $changesLogger;

    /**
     * ReturnRequestBuilder constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param RepositoryInterface $orderReturnRepository
     * @param ReturnNumberGenerator $orderReturnGenerator
     * @param MaxQtyCalculator $maxQtyCalculator
     * @param RmaChangesLogger $changesLogger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RepositoryInterface $orderReturnRepository,
        ReturnNumberGenerator $orderReturnGenerator,
        MaxQtyCalculator $maxQtyCalculator,
        RmaChangesLogger $changesLogger
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->orderReturnGenerator = $orderReturnGenerator;
        $this->maxQtyCalculator = $maxQtyCalculator;
        $this->changesLogger = $changesLogger;
    }

    /**
     * @param string $orderNumber
     * @return OrderReturnInterface
     * @throws Exception
     */
    public function build(string $orderNumber): OrderReturnInterface
    {
        // TODO: this can be replaced by orderLoader / provider or smth
        $order = $this->orderRepository->findOneByNumber($orderNumber);

        if (!$order) {
            $order = $this->orderRepository->findOneByNumber('#' . $orderNumber);
        }
        // END

        if (!$order instanceof OrderInterface) {
            throw new Exception(sprintf('$order must implement %s interface', OrderInterface::class));
        }

        $draftOrderSearchData = [ 'orderNumber' => $orderNumber, 'orderReturnStatus' => OrderReturnInterface::STATUS_DRAFT ];
        $draftOrderReturn = $this->orderReturnRepository->findOneBy($draftOrderSearchData);

        // if draft order already exists then return it
        // ONLY ONE draft order return object per sales order is allowed
        if ($draftOrderReturn instanceof OrderReturnInterface) {
            return $draftOrderReturn;
        }

        $orderReturn = new OrderReturn();

        // populate order data
        $orderReturnNumber = $this->orderReturnGenerator->returnNumberGenerate($orderNumber);
        $orderReturn->setReturnNumber($orderReturnNumber);
        $orderReturn->setChannelCode($order->getChannel()->getCode());
        $orderReturn->setOrderNumber($orderNumber);

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

            if (!$itemVariantCode = $orderItemVariant->getCode()) {
                throw new \Exception('Cannot create OrderItemReturnRequest for OrderItem without code.');
            }

            // TODO: $maxQty should be calculated based on following pattern: $qtyOrdered(orShipped) - $qtyAlreadyReturned
            $originalQty = $item->getQuantity();
            $maxQty = $this->maxQtyCalculator->calculation($orderNumber, $itemVariantCode, $originalQty);

            $orderReturnItem= new OrderReturnItem();
            $orderReturnItem->setUnitPrice($item->getUnitPrice());
            $orderReturnItem->setProductName($item->getProductName());
            $orderReturnItem->setProductSku($orderItemVariant->getCode());
            $orderReturnItem->setMaxQty($maxQty);
            $orderReturnItem->setReturnQty($maxQty);

            $orderReturn->addItem($orderReturnItem);
        }

        //Logger functionality
        $newChangeLogAuthor = new OrderReturnChangeLogAuthor();
        $newChangeLogAuthor->setFirstName($address->getFirstName());
        $newChangeLogAuthor->setLastName($address->getLastName());
        $newChangeLogAuthor->setType('customer');

        $this->changesLogger->add($orderReturnNumber, 'created_draft', '', $newChangeLogAuthor);

        $this->orderReturnRepository->add($orderReturn);

        return $orderReturn;
    }
}
