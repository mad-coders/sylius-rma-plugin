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

namespace Tests\Madcoders\SyliusRmaPlugin\Unit\Services\GracePeriod;

use Doctrine\Persistence\ObjectManager;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriod;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodLog;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodLogInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Services\GracePeriod\GracePeriodManager;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Unit\UnitTestCase;

class GracePeriodManagerTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<RepositoryInterface> */
    private ObjectProphecy $repository;

    /** @var ObjectProphecy<ObjectManager> */
    private ObjectProphecy $objectManager;

    private GracePeriodManager $manager;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(RepositoryInterface::class);
        $this->objectManager = $this->prophesize(ObjectManager::class);

        $gracePeriodFactory = $this->prophesize(FactoryInterface::class);
        $gracePeriodFactory->createNew()->will(fn () => new OrderReturnReasonGracePeriod());
        $logFactory = $this->prophesize(FactoryInterface::class);
        $logFactory->createNew()->will(fn () => new OrderReturnReasonGracePeriodLog());

        $this->manager = new GracePeriodManager(
            $this->repository->reveal(),
            $gracePeriodFactory->reveal(),
            $logFactory->reveal(),
            $this->objectManager->reveal(),
        );
    }

    /** @test */
    function it_grants_a_new_grace_period_and_logs_it()
    {
        $order = $this->order();
        $reason = $this->reason('damaged');
        $this->repository->findOneBy(['order' => $order, 'reason' => $reason])->willReturn(null);

        $persisted = [];
        $this->objectManager->persist(Argument::any())->will(function (array $args) use (&$persisted) {
            $persisted[] = $args[0];
        });
        $this->objectManager->flush()->shouldBeCalledOnce();

        $log = $this->manager->grant($order, $reason, 20, '  Carrier delay  ', $this->author());

        $this->assertNotNull($log);
        $this->assertSame(OrderReturnReasonGracePeriodLogInterface::ACTION_GRANTED, $log->getAction());
        $this->assertSame('damaged', $log->getReasonCode());
        $this->assertNull($log->getPreviousExtraDays());
        $this->assertSame(20, $log->getExtraDays());
        $this->assertSame('Carrier delay', $log->getNote());
        $this->assertSame('Anna', $log->getAuthor()->getFirstName());

        $gracePeriod = $persisted[0];
        $this->assertInstanceOf(OrderReturnReasonGracePeriod::class, $gracePeriod);
        $this->assertSame($order, $gracePeriod->getOrder());
        $this->assertSame($reason, $gracePeriod->getReason());
        $this->assertSame(20, $gracePeriod->getExtraDays());
        $this->assertSame('Carrier delay', $gracePeriod->getNote());
        $this->assertSame($log, $persisted[1]);
    }

    /** @test */
    function it_changes_an_existing_grace_period_and_logs_the_previous_days()
    {
        $order = $this->order();
        $reason = $this->reason('damaged');
        $existing = $this->existingGracePeriod($order, $reason, 10, null);
        $this->repository->findOneBy(['order' => $order, 'reason' => $reason])->willReturn($existing);
        $this->objectManager->persist(Argument::any())->shouldBeCalledTimes(2);
        $this->objectManager->flush()->shouldBeCalledOnce();

        $log = $this->manager->grant($order, $reason, 30, null, $this->author());

        $this->assertNotNull($log);
        $this->assertSame(OrderReturnReasonGracePeriodLogInterface::ACTION_UPDATED, $log->getAction());
        $this->assertSame(10, $log->getPreviousExtraDays());
        $this->assertSame(30, $log->getExtraDays());
        $this->assertSame(30, $existing->getExtraDays());
    }

    /** @test */
    function it_writes_nothing_when_the_grace_period_is_unchanged()
    {
        $order = $this->order();
        $reason = $this->reason('damaged');
        $existing = $this->existingGracePeriod($order, $reason, 10, 'Carrier delay');
        $this->repository->findOneBy(['order' => $order, 'reason' => $reason])->willReturn($existing);
        $this->objectManager->persist(Argument::any())->shouldNotBeCalled();
        $this->objectManager->flush()->shouldNotBeCalled();

        $this->assertNull($this->manager->grant($order, $reason, 10, ' Carrier delay ', $this->author()));
    }

    /** @test */
    function it_stores_a_blank_note_as_no_note()
    {
        $order = $this->order();
        $reason = $this->reason('damaged');
        $this->repository->findOneBy(['order' => $order, 'reason' => $reason])->willReturn(null);
        $this->objectManager->persist(Argument::any())->willReturn(null);
        $this->objectManager->flush()->willReturn(null);

        $log = $this->manager->grant($order, $reason, 5, '   ', $this->author());

        $this->assertNotNull($log);
        $this->assertNull($log->getNote());
    }

    /** @test */
    function it_rejects_extra_days_outside_the_allowed_range()
    {
        $this->objectManager->flush()->shouldNotBeCalled();

        $this->expectException(\InvalidArgumentException::class);

        $this->manager->grant($this->order(), $this->reason('damaged'), 366, null, $this->author());
    }

    /** @test */
    function it_revokes_a_grace_period_and_logs_the_revoked_days()
    {
        $order = $this->order();
        $existing = $this->existingGracePeriod($order, $this->reason('damaged'), 15, 'Carrier delay');
        $this->objectManager->remove($existing)->shouldBeCalledOnce();
        $this->objectManager->persist(Argument::type(OrderReturnReasonGracePeriodLog::class))->shouldBeCalledOnce();
        $this->objectManager->flush()->shouldBeCalledOnce();

        $log = $this->manager->revoke($existing, $this->author());

        $this->assertSame(OrderReturnReasonGracePeriodLogInterface::ACTION_REVOKED, $log->getAction());
        $this->assertSame($order, $log->getOrder());
        $this->assertSame('damaged', $log->getReasonCode());
        $this->assertSame(15, $log->getPreviousExtraDays());
        $this->assertNull($log->getExtraDays());
    }

    private function order(): OrderInterface
    {
        return $this->prophesize(OrderInterface::class)->reveal();
    }

    private function reason(string $code): OrderReturnReasonInterface
    {
        $reason = $this->prophesize(OrderReturnReasonInterface::class);
        $reason->getCode()->willReturn($code);

        return $reason->reveal();
    }

    private function author(): OrderReturnChangeLogAuthor
    {
        $author = new OrderReturnChangeLogAuthor();
        $author->setType('admin');
        $author->setFirstName('Anna');
        $author->setLastName('Admin');

        return $author;
    }

    private function existingGracePeriod(
        OrderInterface $order,
        OrderReturnReasonInterface $reason,
        int $extraDays,
        ?string $note,
    ): OrderReturnReasonGracePeriod {
        $gracePeriod = new OrderReturnReasonGracePeriod();
        $gracePeriod->setOrder($order);
        $gracePeriod->setReason($reason);
        $gracePeriod->setExtraDays($extraDays);
        $gracePeriod->setNote($note);

        return $gracePeriod;
    }
}
