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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Workflow;

use Madcoders\SyliusRmaPlugin\Workflow\OrderReturnWorkflowSubscriber;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

/**
 * Locks the subscriber's event-subscription contract: exactly one handler per return_status
 * transition, bound to the Symfony Workflow `completed` event so the winzou "after" semantics are
 * preserved. The handlers' actual delegation (including the two `withdraw` origins - draft vs
 * withdrawal_request) is exercised end to end by the Behat withdrawal suites, since the callback
 * services they delegate to are `final readonly` and not unit-doubleable.
 */
class OrderReturnWorkflowSubscriberTest extends UnitTestCase
{
    /** @test */
    function it_subscribes_one_handler_per_transition_on_the_completed_event()
    {
        $this->assertSame(
            [
                'workflow.return_status.completed.cancel' => 'onCancel',
                'workflow.return_status.completed.complete' => 'onComplete',
                'workflow.return_status.completed.request_withdrawal' => 'onRequestWithdrawal',
                'workflow.return_status.completed.withdraw' => 'onWithdraw',
                'workflow.return_status.completed.fallback_to_return' => 'onFallbackToReturn',
            ],
            OrderReturnWorkflowSubscriber::getSubscribedEvents(),
        );
    }

    /** @test */
    function every_subscribed_handler_exists_on_the_subscriber()
    {
        foreach (OrderReturnWorkflowSubscriber::getSubscribedEvents() as $method) {
            $this->assertTrue(
                method_exists(OrderReturnWorkflowSubscriber::class, $method),
                sprintf('Subscriber is missing the "%s" handler.', $method),
            );
        }
    }
}
