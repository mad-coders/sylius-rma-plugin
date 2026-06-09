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

namespace Madcoders\SyliusRmaPlugin\Services\Reason;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ChoiceProvider implements ChoiceProviderInterface
{
    /**
     * @param RepositoryInterface<OrderReturnReasonInterface> $orderReturnReasonRepository
     */
    public function __construct(
        private readonly RepositoryInterface $orderReturnReasonRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ReturnReasonEligibilityCheckerInterface $returnReasonEligibilityChecker,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getChoices(OrderReturnInterface $orderReturn): array
    {
        $orderNumber = $orderReturn->getOrderNumber();
        $order = $this->orderRepository->findOneByNumber($orderNumber);

        if (!$order instanceof OrderInterface) {
            $order = $this->orderRepository->findOneByNumber('#' . $orderNumber);
        }

        if (!$order instanceof OrderInterface) {
            return [];
        }

        return $this->createAvailableReasons($order);
    }

    /**
     * @return array<string, string>
     */
    public function createAvailableReasons(OrderInterface $order): array
    {
        if ($order->getState() !== OrderInterface::STATE_FULFILLED) {
            return [];
        }

        $reasons = $this->orderReturnReasonRepository->findBy(['enabled' => true]);
        $availableReasons = [];

        foreach ($reasons as $reason) {
            $reasonCode = $reason->getCode();
            if (null === $reasonCode || '' === $reasonCode) {
                continue;
            }
            $reasonName = $reason->getName();
            if (null === $reasonName || '' === $reasonName) {
                continue;
            }
            if ($this->returnReasonEligibilityChecker->isEligible($order, $reason)) {
                $availableReasons[$reasonCode] = $reasonName;
            }
        }

        return $availableReasons;
    }

    public function getNameByCode(string $code): ?string
    {
        $reason = $this->orderReturnReasonRepository->findOneBy(['code' => $code]);
        if (!$reason instanceof OrderReturnReasonInterface) {
            throw new \Exception('Reason is missing');
        }

        return $reason->getName();
    }
}
