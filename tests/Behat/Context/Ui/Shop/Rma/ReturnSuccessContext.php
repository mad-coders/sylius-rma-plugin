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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Behat\Service\Checker\EmailCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\ReturnSuccessPageInterface;
use Webmozart\Assert\Assert;

class ReturnSuccessContext implements Context
{
    /** @var ReturnSuccessPageInterface */
    private $returnSuccessPage;

    /** @var RepositoryInterface */
    private $orderReturnRepository;

    /** @var EmailCheckerInterface */
    private $emailChecker;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * ReturnSuccessContext constructor
     */
    public function __construct(
        ReturnSuccessPageInterface $returnSuccessPage,
        RepositoryInterface $orderReturnRepository,
        EmailCheckerInterface $emailChecker,
        TranslatorInterface $translator,
    ) {
        $this->returnSuccessPage = $returnSuccessPage;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->emailChecker = $emailChecker;
        $this->translator = $translator;
    }

    /**
     * @Then /^I should be redirected to success page for (latest order)$/
     *
     * @throws \Exception
     */
    public function iShouldBeRedirectToSuccessPage(OrderInterface $order): void
    {
        $returnNumber = $this->findNewReturnFormByOrderNumber($order->getNumber());
        $this->returnSuccessPage->verify(['returnNumber' => $returnNumber]);
    }

    /**
     * Asserts a return with the expected number exists. The "{orderNumber}" placeholder in the
     * expected value is replaced with the latest order number, so the format can be checked
     * without hard-coding the dynamic order number into the feature.
     *
     * @Then /^an order return numbered "([^"]+)" should exist for (latest order)$/
     */
    public function anOrderReturnNumberedShouldExist(string $expectedReturnNumber, OrderInterface $order): void
    {
        $orderNumber = str_replace('#', '', (string) $order->getNumber());
        $expectedReturnNumber = str_replace('{orderNumber}', $orderNumber, $expectedReturnNumber);

        $orderReturn = $this->orderReturnRepository->findOneBy(['returnNumber' => $expectedReturnNumber]);

        Assert::isInstanceOf(
            $orderReturn,
            OrderReturnInterface::class,
            sprintf('Expected an order return numbered "%s" to exist, but none was found.', $expectedReturnNumber),
        );
    }

    /**
     * @Then /^email with order return confirmation should be sent to "([^"]+)" for (latest order)$/
     */
    public function iRecievedConfirmationEmail(string $recipient, OrderInterface $order, string $localeCode = 'en_US'): void
    {
        $returnNumber = $this->findNewReturnFormByOrderNumber($order->getNumber());
        Assert::notNull($returnNumber);

        $message = $this->translator->trans(
            'madcoders_rma.email.order_return_form.greeting',
            ['%name%' => $returnNumber],
            null,
            $localeCode,
        );

        Assert::true($this->emailChecker->hasMessageTo($message, $recipient));
    }

    /**
     * @throws \Exception
     */
    private function findNewReturnFormByOrderNumber(string $orderNumber): string
    {
        $orderReturnList = $this->orderReturnRepository->findBy([
            'orderNumber' => str_replace('#', '', $orderNumber),
            'orderReturnStatus' => 'new',
        ]);

        if (count($orderReturnList) < 1) {
            $orderReturnList = $this->orderReturnRepository->findBy([
                'orderNumber' => $orderNumber,
                'orderReturnStatus' => 'new',
            ]);
        }

        if (!($orderReturn = $orderReturnList[0]) instanceof OrderReturnInterface) {
            throw new \Exception(sprintf('Could not find a draft for order "%s"', $orderNumber));
        }

        return $orderReturn->getReturnNumber();
    }
}
