<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Test\Services\EmailCheckerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\ReturnSuccessPageInterface;
use Webmozart\Assert\Assert;

/**
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS Team (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */
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
     *
     * @param ReturnSuccessPageInterface $returnSuccessPage
     * @param RepositoryInterface $orderReturnRepository
     * @param EmailCheckerInterface $emailChecker
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ReturnSuccessPageInterface $returnSuccessPage,
        RepositoryInterface $orderReturnRepository,
        EmailCheckerInterface $emailChecker,
        TranslatorInterface $translator
    )
    {
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
     * @Then /^email with order return confirmation should be sent to "([^"]+)" for (latest order)$/
     */
    public function iRecievedConfirmationEmail(string $recipient, OrderInterface $order, string $localeCode = 'en_US'): void
    {
        $returnNumber = $this->findNewReturnFormByOrderNumber($order->getNumber());
        Assert::notNull($returnNumber);

        $message = $this->translator->trans(
            'madcoders_rma.email.order_return_form.greeting',
            [ '%name%' => $returnNumber ],
            null,
            $localeCode
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
            'orderReturnStatus' => 'new'
        ]);

        if (count($orderReturnList) < 1 ) {
            $orderReturnList = $this->orderReturnRepository->findBy([
                'orderNumber' => $orderNumber,
                'orderReturnStatus' => 'new'
            ]);
        }

        if (!($orderReturn = $orderReturnList[0]) instanceof OrderReturnInterface) {
            throw new \Exception(sprintf('Could not find a draft for order "%s"', $orderNumber));
        }

        return $orderReturn->getReturnNumber();
    }
}
