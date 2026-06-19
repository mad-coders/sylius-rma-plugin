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

namespace Madcoders\SyliusRmaPlugin\Services;

use Exception;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnItem;
use Madcoders\SyliusRmaPlugin\Generator\ReturnNumberGeneratorInterface;
use Madcoders\SyliusRmaPlugin\Provider\OrderByNumberProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnRequestBuilder
{
    /**
     * @param RepositoryInterface<OrderReturnInterface> $orderReturnRepository
     */
    public function __construct(
        private readonly RepositoryInterface $orderReturnRepository,
        private readonly ReturnNumberGeneratorInterface $orderReturnGenerator,
        private readonly MaxQtyCalculator $maxQtyCalculator,
        private readonly OrderByNumberProviderInterface $orderByNumberProvider,
        private readonly RmaChangesLogger $changesLogger,
        private readonly ProductReturnabilityCheckerInterface $productReturnabilityChecker,
    ) {
    }

    /**
     * @throws Exception
     */
    public function build(string $orderNumber): OrderReturnInterface
    {
        $order = $this->orderByNumberProvider->findOneByNumber($orderNumber);

        if (!$order instanceof OrderInterface) {
            throw new Exception(sprintf('$order must implement %s interface', OrderInterface::class));
        }

        $draftOrderSearchData = ['orderNumber' => $orderNumber, 'orderReturnStatus' => OrderReturnInterface::STATUS_DRAFT];
        $draftOrderReturn = $this->orderReturnRepository->findOneBy($draftOrderSearchData);

        // if draft order already exists then return it
        // ONLY ONE draft order return object per sales order is allowed
        if ($draftOrderReturn instanceof OrderReturnInterface) {
            return $draftOrderReturn;
        }

        $orderReturn = new OrderReturn();

        // populate order data
        $orderReturnNumber = $this->orderReturnGenerator->generate($order);
        $orderReturn->setReturnNumber($orderReturnNumber);

        $channel = $order->getChannel();
        if (null === $channel) {
            throw new Exception('Order channel is missing');
        }
        $channelCode = $channel->getCode();
        if (null === $channelCode) {
            throw new Exception('Order channel code is missing');
        }
        $orderReturn->setChannelCode($channelCode);
        $orderReturn->setOrderNumber($orderNumber);

        // check if customer exists
        $customer = $order->getCustomer();
        if (null === $customer) {
            throw new Exception('Customer is missing');
        }

        // populate customer email
        $orderReturn->setCustomerEmail($customer->getEmail());

        // set customer number
        $customerId = $customer->getId();
        $orderReturn->setCustomerNumber(is_scalar($customerId) ? (string) $customerId : '');

        // check if address exists
        $address = $order->getBillingAddress();
        if (null === $address) {
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

            $itemVariantCode = $orderItemVariant->getCode();
            if (null === $itemVariantCode || '' === $itemVariantCode) {
                throw new \Exception('Cannot create OrderItemReturnRequest for OrderItem without code.');
            }

            // Item-level eligibility: a non-returnable variant is never offered for return, so it is
            // not added to the draft (and therefore never reaches the return form or is persisted).
            if (!$this->productReturnabilityChecker->isReturnable($orderItemVariant)) {
                continue;
            }

            // TODO: $maxQty should be calculated based on following pattern: $qtyOrdered(orShipped) - $qtyAlreadyReturned
            $originalQty = $item->getQuantity();
            $maxQty = $this->maxQtyCalculator->calculation($orderNumber, $itemVariantCode, $originalQty);

            $orderReturnItem = new OrderReturnItem();
            $orderReturnItem->setUnitPrice($item->getUnitPrice());
            $orderReturnItem->setProductName($item->getProductName());
            $orderReturnItem->setProductSku($itemVariantCode);
            $orderReturnItem->setMaxQty($maxQty);
            $orderReturnItem->setReturnQty($maxQty);

            $orderReturn->addItem($orderReturnItem);
        }

        //Logger functionality
        $newChangeLogAuthor = new OrderReturnChangeLogAuthor();
        $newChangeLogAuthor->setFirstName($address->getFirstName() ?? '');
        $newChangeLogAuthor->setLastName($address->getLastName() ?? '');
        $newChangeLogAuthor->setType('customer');

        $this->changesLogger->add($orderReturnNumber, 'created_draft', '', $newChangeLogAuthor);

        $this->orderReturnRepository->add($orderReturn);

        return $orderReturn;
    }
}
