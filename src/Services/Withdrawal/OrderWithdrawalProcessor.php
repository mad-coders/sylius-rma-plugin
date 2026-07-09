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

namespace Madcoders\SyliusRmaPlugin\Services\Withdrawal;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\OrderTransitions;

/**
 * Orchestrates the unpaid withdrawal path: cancels the Sylius order via the core
 * sylius_order state machine, then drives the return to the terminal "withdrawn" state.
 *
 * Both transitions are checked up front; if either is not allowed nothing is applied, so a
 * non-cancellable order never leaves an orphaned "withdrawn" return behind.
 */
final readonly class OrderWithdrawalProcessor implements OrderWithdrawalProcessorInterface
{
    public function __construct(
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function process(OrderInterface $order, OrderReturnInterface $orderReturn): void
    {
        if (!$this->stateMachine->can($order, OrderTransitions::GRAPH, OrderTransitions::TRANSITION_CANCEL)) {
            throw new \RuntimeException(sprintf('Order "%s" cannot be cancelled.', (string) $order->getNumber()));
        }

        if (!$this->stateMachine->can($orderReturn, OrderReturnInterface::GRAPH, OrderReturnInterface::TRANSITION_WITHDRAW)) {
            throw new \RuntimeException(sprintf('Return "%s" cannot be withdrawn.', $orderReturn->getReturnNumber()));
        }

        $this->stateMachine->apply($order, OrderTransitions::GRAPH, OrderTransitions::TRANSITION_CANCEL);
        $this->stateMachine->apply($orderReturn, OrderReturnInterface::GRAPH, OrderReturnInterface::TRANSITION_WITHDRAW);
    }
}
