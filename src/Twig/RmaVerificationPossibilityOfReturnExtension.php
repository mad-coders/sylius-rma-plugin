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

use Madcoders\SyliusRmaPlugin\Services\RmaVerificationPossibilityOfReturn;
use Sylius\Component\Core\Model\OrderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RmaVerificationPossibilityOfReturnExtension extends AbstractExtension
{
    /**
     * RmaVerificationPossibilityOfReturnExtension constructor.
     */
    public function __construct(private readonly RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn)
    {
    }

    /** @inheritdoc */
    public function getFunctions()
    {
        return [
            new TwigFunction('rma_order_has_items_to_returned_view', $this->verificationPossibilityOfReturn(...)),
        ];
    }

    /**
     * @throws \Exception
     */
    public function verificationPossibilityOfReturn(OrderInterface $order): bool
    {
        return $this->verificationPossibilityOfReturn->verificationForButtonRender($order);
    }
}
