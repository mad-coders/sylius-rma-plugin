<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\Rma;

use Behat\Behat\Context\Context;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Component\Core\Test\Services\EmailCheckerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\FlashNotificationContextTrait;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\AuthPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\StartPageInterface;
use Webmozart\Assert\Assert;

class AuthContext implements Context
{
    use FlashNotificationContextTrait;

    /** @var StartPageInterface */
    private $startPage;

    /** @var AuthPageInterface */
    private $authPage;

    /** @var RepositoryInterface */
    private $authCodeRepository;

    /** @var EmailCheckerInterface */
    private $emailChecker;

    /** @var TranslatorInterface  */
    private $translator;

    public function __construct(
        StartPageInterface $startPage,
        AuthPageInterface $authPage,
        RepositoryInterface $authCodeRepository,
        EmailCheckerInterface $emailChecker,
        TranslatorInterface $translator
    )
    {
        $this->startPage = $startPage;
        $this->authCodeRepository = $authCodeRepository;
        $this->authPage = $authPage;
        $this->emailChecker = $emailChecker;
        $this->translator = $translator;
    }

    /**
     * @Then I can see order number input field
     */
    public function iCanSeeOrderNumberField(): void
    {
        Assert::true($this->startPage->hasOrderNumberField());
    }

    /**
     * @Given I am on RMA start page
     */
    public function iAmOnRmaStartPage(): void
    {
        $this->startPage->open();
    }

    /**
     * @When I visit RMA start page
     */
    public function visitStartPage(): void
    {
        $this->startPage->open();
    }

    /**
     * @When I enter :orderNumber in order number input filed
     */
    public function enterOrderNumber(string $orderNumber): void
    {
        $this->startPage->getOrderNumberField()->setValue($orderNumber);
    }

    /**
     * @When I submit the form
     */
    public function submitForm(): void
    {
        $this->startPage->getSubmitButton()->click();
    }

    /**
     * @Then I should be redirected to auth code page
     */
    public function iShouldBeOnAuthCodePage()
    {
        $authCode = $this->getLastAuthCode();
        Assert::notNull($authCode);

        $this->authPage->verify(['code' => $authCode->getHash()]);
    }

    /**
     * @Then I should be redirected to auth code page with :hash
     */
    public function iShouldBeOnAuthCodePageWithHash(string $hash)
    {
        $this->authPage->verify(['code' => $hash]);
    }

    /**
     * @Then email with auth code has been sent to :recipient
     */
    public function iRecievedAuthCodeEmail(string $recipient, string $localeCode = 'en_US'): void
    {
        $authCode = $this->getLastAuthCode();
        Assert::notNull($authCode);
        Assert::notNull($authCode->getAuthCode());

        $message = $this->translator->trans(
            'madcoders_rma.email.order_return_auth_email.auth_code_info',
            [ '%auth_code%' => $authCode->getAuthCode() ],
            null,
            $localeCode
        );

        $this->emailChecker->hasMessageTo($message, $recipient);
    }

    private function getLastAuthCode(): ?AuthCodeInterface
    {
        $authCode = $this->authCodeRepository->findBy([], ['id' => 'DESC']);
        if (count($authCode) === 0) {
            return null;
        }

        return $authCode[0];
    }

    /**
     * @return SymfonyPageInterface
     */
    private function getPage(): SymfonyPageInterface
    {
        return $this->authPage;
    }
}
