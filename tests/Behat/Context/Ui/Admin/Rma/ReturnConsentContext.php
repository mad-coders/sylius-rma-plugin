<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Admin\Rma;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Madcoders\SyliusRmaPlugin\Entity\AuthCodeInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Test\Services\EmailCheckerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnConsent\CreatePageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnConsent\IndexPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnConsent\UpdatePageInterface;
use Webmozart\Assert\Assert;

class ReturnConsentContext implements Context
{
    /** @var IndexPageInterface */
    private $returnConsentIndexPage;

    /** @var CreatePageInterface  */
    private $returnConsentCreatePage;

    /** @var UpdatePageInterface */
    private $returnConsentUpdatePage;

    /** @var RepositoryInterface */
    private $orderReturnConsentRepository;

    /** @var SharedStorageInterface */
    private $sharedStorage;

    /**
     * ReturnConsentContext constructor.
     *
     * @param IndexPageInterface $returnConsentIndexPage
     * @param CreatePageInterface $returnConsentCreatePage
     * @param UpdatePageInterface $returnConsentUpdatePage
     * @param RepositoryInterface $orderReturnConsentRepository
     * @param SharedStorageInterface $sharedStorage
     */
    public function __construct(
        IndexPageInterface $returnConsentIndexPage,
        CreatePageInterface $returnConsentCreatePage,
        UpdatePageInterface $returnConsentUpdatePage,
        RepositoryInterface $orderReturnConsentRepository,
        SharedStorageInterface $sharedStorage
    )
    {
        $this->returnConsentIndexPage = $returnConsentIndexPage;
        $this->returnConsentCreatePage = $returnConsentCreatePage;
        $this->sharedStorage = $sharedStorage;
        $this->returnConsentUpdatePage = $returnConsentUpdatePage;
        $this->orderReturnConsentRepository = $orderReturnConsentRepository;
    }

    /**
     * @Given  I am on return consent index page
     */
    public function iAmOnReturnConsentIndexPage(): void
    {
        $this->returnConsentIndexPage->open();
    }

    /**
     * @Given  I am on return consent edit page for consent code :consentCode
     */
    public function iAmOnReturnConsentUpdatePage(string $consentCode): void
    {
        $returnConsentId = $this->findReturnConsentIdByCode($consentCode);
        $this->returnConsentUpdatePage->open(['id' => $returnConsentId]);
    }

    /**
     * @Given I want to create a new return consent
     * @Given I want to add a new return consent
     * @When I click create button
     */
    public function iCreateNewReturnConsent()
    {
        $this->returnConsentCreatePage->open();
    }

    /**
     * @When I should be redirected to return consent create page
     */
    public function iShouldBeOnReturnConsentCreatePage()
    {
        $this->returnConsentCreatePage->verify();
    }

    /**
     * @When I fill create consent form with following data:
     */
    public function iFillCreateForm(TableNode $table)
    {
        $formName = 'madcoders_rma_return_consent';
        $localeCode = $this->getAdminLocaleCode();
        foreach($table as $row) {
            $translationPrefix = $row['type'] === 'translations' ? 'translations_'. $localeCode . '_' : '';
            $locator = sprintf('%s_%s%s', $formName, $translationPrefix, $row['field']);

            $this->returnConsentCreatePage->choosesFormElement($row['value'], $locator);
        }
    }

    /**
     * @When I click submit button
     */
    public function iClickSubmitButton()
    {
        $this->returnConsentCreatePage->create();
    }

    /**
     * @When I click Save changes button
     */
    public function iClickSaveChangesButton()
    {
        $this->returnConsentUpdatePage->saveChanges();
    }

    /**
     * @When I delete the :returnConsentName return consent
     */
    public function iDeleteReturnConsent(string $returnConsentName)
    {
        $this->returnConsentIndexPage->deleteResourceOnPage(['name' => $returnConsentName]);
    }

    /**
     * @When I change edit consent form with following data:
     */
    public function iFillEditForm(TableNode $table)
    {
        $formName = 'madcoders_rma_return_consent';
        $localeCode = $this->getAdminLocaleCode();
        foreach($table as $row) {
            $translationPrefix = $row['type'] === 'translations' ? 'translations_'. $localeCode . '_' : '';
            $locator = sprintf('%s_%s%s', $formName, $translationPrefix, $row['field']);

            $this->returnConsentUpdatePage->choosesFormElement($row['value'], $locator);
        }
    }

    private function findReturnConsentIdByCode(string $code): ?int
    {
        $returnConsent = $this->orderReturnConsentRepository->findOneBy(['code' => $code]);
        Assert::notNull($returnConsent);

        return $returnConsent->getId();
    }

    private function getAdminLocaleCode(): string
    {
        /** @var AdminUserInterface $adminUser */
        $adminUser = $this->sharedStorage->get('administrator');
        return $adminUser->getLocaleCode();
    }
}
