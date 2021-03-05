<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services;

use Madcoders\SyliusRmaPlugin\Entity\OrderItemReturnRequest;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturn;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnRequest;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;

class ReturnRequestBuilder
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function build(string $orderNumber): OrderReturn
    {
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        $orderRequest = new OrderReturnRequest();

        /**
         * @var OrderItem $item
         */
        foreach ($order->getItems() as $item) {
            $orderItemVariant = $item->getVariant();

            if (!$orderItemVariant instanceof ProductVariantInterface) {
                throw new \Exception(sprintf('$item->getVariant() must return %s', ProductVariantInterface::class));
            }

            if (!$orderItemVariant->getCode()) {
                throw new \Exception('Cannot create OrderItemReturnRequest for OrderItem without code.');
            }

            $orderItemReturnRequest = new OrderItemReturnRequest(
                $item->getProductName(),
                $orderItemVariant->getCode(),
                $item->getQuantity(),
                0,
            );

            $orderRequest->addItem($orderItemReturnRequest);
         }

        return $orderRequest;
    }
}
