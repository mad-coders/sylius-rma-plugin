<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Twig;

use Madcoders\SyliusRmaPlugin\Services\RmaVerificationPossibilityOfReturn;
use Sylius\Component\Core\Model\OrderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RmaVerificationPossibilityOfReturnExtension extends AbstractExtension
{
    /** @var RmaVerificationPossibilityOfReturn */
    private $verificationPossibilityOfReturn;

    /**
     * RmaVerificationPossibilityOfReturnExtension constructor.
     * @param RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn
     */
    public function __construct(
        RmaVerificationPossibilityOfReturn $verificationPossibilityOfReturn
    )
    {
        $this->verificationPossibilityOfReturn = $verificationPossibilityOfReturn;
    }

    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new TwigFunction('rma_order_has_items_to_returned_view', [$this, 'verificationPossibilityOfReturn']),
        ];
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @throws \Exception
     */
    public function verificationPossibilityOfReturn(OrderInterface $order): bool
    {
        return $this->verificationPossibilityOfReturn->verificationForButtonRender($order);
    }
}
