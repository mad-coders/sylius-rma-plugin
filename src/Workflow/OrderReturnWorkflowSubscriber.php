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

namespace Madcoders\SyliusRmaPlugin\Workflow;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Madcoders\SyliusRmaPlugin\Services\Callbacks\UpdatedChangelogOnCancel;
use Madcoders\SyliusRmaPlugin\Services\Callbacks\UpdatedChangelogOnComplete;
use Madcoders\SyliusRmaPlugin\Services\Callbacks\WithdrawalCompletedNotifier;
use Madcoders\SyliusRmaPlugin\Services\Callbacks\WithdrawalRequestNotifier;
use Madcoders\SyliusRmaPlugin\Services\Callbacks\WithdrawalResolutionNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

/**
 * Re-wires the winzou "after" callbacks of the return_status graph as Symfony Workflow
 * "completed" event listeners, so the changelog updates and withdrawal notifications keep
 * firing after each transition on the migrated (symfony/workflow) state machine.
 *
 * The `withdraw` transition exists twice in the graph (from draft and from withdrawal_request);
 * both dispatch the same `workflow.return_status.completed.withdraw` event, so onWithdraw()
 * branches on the completed transition's from-place to preserve the two winzou callbacks.
 */
final readonly class OrderReturnWorkflowSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UpdatedChangelogOnCancel $updatedChangelogOnCancel,
        private UpdatedChangelogOnComplete $updatedChangelogOnComplete,
        private WithdrawalRequestNotifier $withdrawalRequestNotifier,
        private WithdrawalCompletedNotifier $withdrawalCompletedNotifier,
        private WithdrawalResolutionNotifier $withdrawalResolutionNotifier,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.return_status.completed.cancel' => 'onCancel',
            'workflow.return_status.completed.complete' => 'onComplete',
            'workflow.return_status.completed.request_withdrawal' => 'onRequestWithdrawal',
            'workflow.return_status.completed.withdraw' => 'onWithdraw',
            'workflow.return_status.completed.fallback_to_return' => 'onFallbackToReturn',
        ];
    }

    public function onCancel(CompletedEvent $event): void
    {
        $this->updatedChangelogOnCancel->updatedChangelogOnCancel($this->orderReturn($event));
    }

    public function onComplete(CompletedEvent $event): void
    {
        $this->updatedChangelogOnComplete->UpdatedChangelogOnComplete($this->orderReturn($event));
    }

    public function onRequestWithdrawal(CompletedEvent $event): void
    {
        $this->withdrawalRequestNotifier->onRequestWithdrawal($this->orderReturn($event));
    }

    public function onWithdraw(CompletedEvent $event): void
    {
        $orderReturn = $this->orderReturn($event);
        $froms = $event->getTransition()?->getFroms() ?? [];

        // withdraw FROM draft = instant/fast-forward withdrawal (customer-initiated).
        if (in_array(OrderReturnInterface::STATUS_DRAFT, $froms, true)) {
            $this->withdrawalCompletedNotifier->onWithdraw($orderReturn);

            return;
        }

        // withdraw FROM withdrawal_request = admin-approved withdrawal (admin-initiated).
        if (in_array(OrderReturnInterface::STATUS_WITHDRAWAL_REQUEST, $froms, true)) {
            $this->withdrawalResolutionNotifier->onConfirmWithdrawal($orderReturn);
        }
    }

    public function onFallbackToReturn(CompletedEvent $event): void
    {
        $this->withdrawalResolutionNotifier->onFallbackToReturn($this->orderReturn($event));
    }

    private function orderReturn(CompletedEvent $event): OrderReturnInterface
    {
        $subject = $event->getSubject();
        Assert::isInstanceOf($subject, OrderReturnInterface::class);

        return $subject;
    }
}
