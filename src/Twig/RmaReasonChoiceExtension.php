<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Twig;

use Madcoders\SyliusRmaPlugin\Services\Reason\ChoiceProviderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RmaReasonChoiceExtension  extends AbstractExtension
{
    /** @var ChoiceProviderInterface */
    private $reasonChoiceProvider;

    public function __construct(ChoiceProviderInterface $reasonChoiceProvider)
    {
        $this->reasonChoiceProvider = $reasonChoiceProvider;
    }

    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new TwigFunction('rma_reason_name_view', [$this, 'findReasonNameByCode']),
        ];
    }

    public function findReasonNameByCode(string $code): ?string
    {
        return $this->reasonChoiceProvider->getNameByCode($code);
    }
}
