<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services\Reason;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;

interface ChoiceProviderInterface
{
    /**
     * @param OrderReturnInterface $orderReturn
     *
     * @return array
     */
    public function getChoices(OrderReturnInterface $orderReturn): array;
}
