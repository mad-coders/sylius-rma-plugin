<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Services\Reason;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;

class ChoiceProvider implements ChoiceProviderInterface
{
    /**
     * @param OrderReturnInterface $orderReturn
     *
     * @return array
     */
    public function getChoices(OrderReturnInterface $orderReturn): array
    {
        return [
            'option_a' => 'Option A',
            'option_b' => 'Option B',
        ];
    }
}
