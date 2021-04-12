<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services\Reason;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ChoiceProvider implements ChoiceProviderInterface
{
    /** @var RepositoryInterface */
    private $orderReturnReasonRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * ChoiceProvider constructor.
     * @param RepositoryInterface $orderReturnReasonRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        RepositoryInterface $orderReturnReasonRepository,
        OrderRepositoryInterface $orderRepository
    )
    {
        $this->orderReturnReasonRepository = $orderReturnReasonRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param OrderReturnInterface $orderReturn
     *
     * @return array
     */
    public function getChoices(OrderReturnInterface $orderReturn): array
    {
        $orderNumber = $orderReturn->getOrderNumber();
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        if (!$order instanceof OrderInterface) {
            return [];
        }

        $orderShipment = $order->getShipments()->first();
        if (!$orderShipment instanceof ShipmentInterface) {
            return [];
        }

        $shipmentDate = $orderShipment->getShippedAt();
        $dateNow = new \DateTime('@'.strtotime('now'));
        $daysAreGone = $shipmentDate->diff($dateNow)->d;

        $reasons = $this->orderReturnReasonRepository->findBy(['enabled' => true]);
        $availableReasons = [];

        foreach ($reasons as $reason) {
            if (!$reason instanceof OrderReturnReasonInterface) {
                continue;
            }
            if (!$reasonCode = $reason->getCode()) {
                continue;
            }
            if (!$reasonName = $reason->getName()) {
                continue;
            }
            if ($reason->getDeadlineToReturn() >=  $daysAreGone) {
                $availableReasons[$reasonCode] = $reasonName;
            }
        }

        return $availableReasons;
    }

    public function getNameByCode(string $code): ?string
    {
        $reason = $this->orderReturnReasonRepository->findOneBy(array('code' => $code));
        if (!$reason instanceof OrderReturnReasonInterface) {
            throw new \Exception('Reason is missing');
        }

        return $reason->getName();
    }
}
