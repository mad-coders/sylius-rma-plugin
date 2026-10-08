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

namespace Madcoders\SyliusRmaPlugin\Services\GracePeriod;

use Doctrine\Persistence\ObjectManager;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonGracePeriodLogInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

/**
 * Writes grace periods together with their audit entry, in one flush (#67).
 */
final readonly class GracePeriodManager implements GracePeriodManagerInterface
{
    /**
     * @param RepositoryInterface<OrderReturnReasonGracePeriodInterface> $gracePeriodRepository
     * @param FactoryInterface<OrderReturnReasonGracePeriodInterface> $gracePeriodFactory
     * @param FactoryInterface<OrderReturnReasonGracePeriodLogInterface> $gracePeriodLogFactory
     */
    public function __construct(
        private RepositoryInterface $gracePeriodRepository,
        private FactoryInterface $gracePeriodFactory,
        private FactoryInterface $gracePeriodLogFactory,
        private ObjectManager $objectManager,
    ) {
    }

    public function grant(
        OrderInterface $order,
        OrderReturnReasonInterface $reason,
        int $extraDays,
        ?string $note,
        OrderReturnChangeLogAuthor $author,
    ): ?OrderReturnReasonGracePeriodLogInterface {
        Assert::range(
            $extraDays,
            OrderReturnReasonGracePeriodInterface::MIN_EXTRA_DAYS,
            OrderReturnReasonGracePeriodInterface::MAX_EXTRA_DAYS,
        );
        $note = $this->normalizeNote($note);

        $gracePeriod = $this->gracePeriodRepository->findOneBy(['order' => $order, 'reason' => $reason]);
        if ($gracePeriod instanceof OrderReturnReasonGracePeriodInterface) {
            if ($gracePeriod->getExtraDays() === $extraDays && $gracePeriod->getNote() === $note) {
                return null;
            }

            $action = OrderReturnReasonGracePeriodLogInterface::ACTION_UPDATED;
            $previousExtraDays = $gracePeriod->getExtraDays();
        } else {
            $gracePeriod = $this->gracePeriodFactory->createNew();
            $gracePeriod->setOrder($order);
            $gracePeriod->setReason($reason);

            $action = OrderReturnReasonGracePeriodLogInterface::ACTION_GRANTED;
            $previousExtraDays = null;
        }

        $gracePeriod->setExtraDays($extraDays);
        $gracePeriod->setNote($note);

        $log = $this->createLog($order, $reason, $action, $previousExtraDays, $extraDays, $note, $author);

        $this->objectManager->persist($gracePeriod);
        $this->objectManager->persist($log);
        $this->objectManager->flush();

        return $log;
    }

    public function revoke(
        OrderReturnReasonGracePeriodInterface $gracePeriod,
        OrderReturnChangeLogAuthor $author,
    ): OrderReturnReasonGracePeriodLogInterface {
        $log = $this->createLog(
            $gracePeriod->getOrder(),
            $gracePeriod->getReason(),
            OrderReturnReasonGracePeriodLogInterface::ACTION_REVOKED,
            $gracePeriod->getExtraDays(),
            null,
            null,
            $author,
        );

        $this->objectManager->remove($gracePeriod);
        $this->objectManager->persist($log);
        $this->objectManager->flush();

        return $log;
    }

    private function createLog(
        OrderInterface $order,
        OrderReturnReasonInterface $reason,
        string $action,
        ?int $previousExtraDays,
        ?int $extraDays,
        ?string $note,
        OrderReturnChangeLogAuthor $author,
    ): OrderReturnReasonGracePeriodLogInterface {
        $reasonCode = $reason->getCode();
        Assert::notNull($reasonCode, 'A grace period needs a return reason with a code.');

        $log = $this->gracePeriodLogFactory->createNew();
        $log->setOrder($order);
        $log->setReasonCode($reasonCode);
        $log->setAction($action);
        $log->setPreviousExtraDays($previousExtraDays);
        $log->setExtraDays($extraDays);
        $log->setNote($note);
        $log->setAuthor($author);

        return $log;
    }

    private function normalizeNote(?string $note): ?string
    {
        $note = null === $note ? '' : trim($note);

        return '' === $note ? null : $note;
    }
}
