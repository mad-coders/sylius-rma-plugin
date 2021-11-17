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

    public function findReasonNameByCode(?string $code): ?string
    {
        if (!is_string($code)) {
            return null;
        }

        return $this->reasonChoiceProvider->getNameByCode($code);
    }
}
