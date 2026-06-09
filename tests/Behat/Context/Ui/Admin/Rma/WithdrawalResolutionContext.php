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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Admin\Rma;

use Behat\Behat\Context\Context;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLog;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Behat\Service\Checker\EmailCheckerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

final class WithdrawalResolutionContext implements Context
{
    /**
     * @param RepositoryInterface<OrderReturnInterface> $orderReturnRepository
     * @param RepositoryInterface<OrderReturnChangeLog> $changeLogRepository
     */
    public function __construct(
        private readonly RepositoryInterface $orderReturnRepository,
        private readonly RepositoryInterface $changeLogRepository,
        private readonly EmailCheckerInterface $emailChecker,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @Then /^order return "([^"]+)" should have a "([^"]+)" change-log entry authored by an administrator$/
     */
    public function orderReturnShouldHaveChangeLogEntryAuthoredByAnAdministrator(string $returnNumber, string $type): void
    {
        $changeLog = $this->changeLogRepository->findOneBy(['returnNumber' => $returnNumber, 'type' => $type]);
        Assert::isInstanceOf($changeLog, OrderReturnChangeLog::class);
        Assert::same($changeLog->getAuthor()->getType(), 'admin');
    }

    /**
     * @Then /^a withdrawal "([^"]+)" email should be sent to "([^"]+)" for order return "([^"]+)"$/
     */
    public function aWithdrawalEmailShouldBeSentForOrderReturn(string $type, string $recipient, string $returnNumber, string $localeCode = 'en_US'): void
    {
        $orderReturn = $this->orderReturnRepository->findOneBy(['returnNumber' => $returnNumber]);
        Assert::isInstanceOf($orderReturn, OrderReturnInterface::class);

        $message = $this->translator->trans(
            sprintf('madcoders_rma.email.withdrawal_%s.info', $type),
            ['%orderNumber%' => $orderReturn->getOrderNumber()],
            null,
            $localeCode,
        );

        Assert::true($this->emailChecker->hasMessageTo($message, $recipient));
    }
}
