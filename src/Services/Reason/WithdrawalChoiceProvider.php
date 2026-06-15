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
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * Reason provider for the pre-shipment withdrawal flow. Unlike {@see ChoiceProvider}, it offers
 * every enabled reason without the post-shipment guards (order must be fulfilled, deadline measured
 * from the shipment date): a withdrawn order has not shipped yet, so there is no shipment deadline
 * to honour.
 */
class WithdrawalChoiceProvider implements ChoiceProviderInterface
{
    /**
     * @param RepositoryInterface<OrderReturnReasonInterface> $orderReturnReasonRepository
     */
    public function __construct(
        private readonly RepositoryInterface $orderReturnReasonRepository,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getChoices(OrderReturnInterface $orderReturn): array
    {
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
            $availableReasons[$reasonCode] = $reasonName;
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
