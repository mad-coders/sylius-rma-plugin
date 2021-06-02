<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Admin\Rma;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnReason\CreatePageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnReason\IndexPageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnReason\UpdatePageInterface;
use Webmozart\Assert\Assert;

class ReturnReasonContext implements Context
{
    /** @var IndexPageInterface */
    private $returnReasonIndexPage;

    /** @var CreatePageInterface */
    private $returnReasonCreatePage;

    /** @var UpdatePageInterface */
    private $returnReasonUpdatePage;

    /** @var RepositoryInterface */
    private $orderReturnReasonRepository;

    /** @var SharedStorageInterface */
    private $sharedStorage;

    /**
     * ReturnReasonContext constructor.
     *
     * @param IndexPageInterface $returnReasonIndexPage
     * @param CreatePageInterface $returnReasonCreatePage
     * @param UpdatePageInterface $returnReasonUpdatePage
     * @param SharedStorageInterface $sharedStorage
     */
    public function __construct(
        IndexPageInterface $returnReasonIndexPage,
        CreatePageInterface $returnReasonCreatePage,
        UpdatePageInterface $returnReasonUpdatePage,
        RepositoryInterface $orderReturnReasonRepository,
        SharedStorageInterface $sharedStorage
    )
    {
        $this->returnReasonIndexPage = $returnReasonIndexPage;
        $this->returnReasonCreatePage = $returnReasonCreatePage;
        $this->returnReasonUpdatePage = $returnReasonUpdatePage;
        $this->orderReturnReasonRepository = $orderReturnReasonRepository;
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @Given I am on return reason index page
     */
    public function iAmOnReturnReasonIndexPage(): void
    {
        $this->returnReasonIndexPage->open();
    }

    /**
     * @Given I am on return reason edit page for reason code :reasonCode
     */
    public function iAmOnReturnReasonUpdatePage(string $reasonCode): void
    {
        $returnReasonId = $this->findReturnReasonIdByCode($reasonCode);
        $this->returnReasonUpdatePage->open(['id' => $returnReasonId]);
    }

    /**
     * @Given I want to create a new return reason
     * @Given I want to add a new return reason
     * @When I click create button
     */
    public function iCreateNewReturnReason()
    {
        $this->returnReasonCreatePage->open();
    }

    /**
     * @When I should be redirected to return reason create page
     */
    public function iShouldBeOnReturnReasonCreatePage()
    {
        $this->returnReasonCreatePage->verify();
    }

    /**
     * @When I fill create form with following data:
     */
    public function iFillCreateForm(TableNode $table)
    {
        $formName = 'madcoders_rma_return_reason';
        $localeCode = $this->getAdminLocaleCode();
        foreach($table as $row) {
            $translationPrefix = $row['type'] === 'translations' ? 'translations_'. $localeCode . '_' : '';
            $locator = sprintf('%s_%s%s', $formName, $translationPrefix, $row['field']);

            $this->returnReasonCreatePage->choosesFormElement($row['value'], $locator);
        }
    }

    /**
     * @When I change edit form with following data:
     */
    public function iFillEditForm(TableNode $table)
    {
        $formName = 'madcoders_rma_return_reason';
        $localeCode = $this->getAdminLocaleCode();
        foreach($table as $row) {
            $translationPrefix = $row['type'] === 'translations' ? 'translations_'. $localeCode . '_' : '';
            $locator = sprintf('%s_%s%s', $formName, $translationPrefix, $row['field']);

            $this->returnReasonUpdatePage->choosesFormElement($row['value'], $locator);
        }
    }

    /**
     * @When I delete the :returnReasonName return reason
     */
    public function iDeleteReturnReason(string $returnReasonName)
    {
        $this->returnReasonIndexPage->deleteResourceOnPage(['name' => $returnReasonName]);
    }

    /**
     * @When I click Save changes button
     */
    public function iClickSaveChangesButton()
    {
        $this->returnReasonUpdatePage->saveChanges();
    }

    /**
     * @When I click submit button
     */
    public function iClickSubmitButton()
    {
        $this->returnReasonCreatePage->create();
    }

    private function findReturnReasonIdByCode(string $code): ?int
    {
       $returnReason = $this->orderReturnReasonRepository->findOneBy(['code' => $code]);
       Assert::notNull($returnReason);

        return $returnReason->getId();
    }

    private function getAdminLocaleCode(): string
    {
        /** @var AdminUserInterface $adminUser */
        $adminUser = $this->sharedStorage->get('administrator');
        return $adminUser->getLocaleCode();
    }

    /**
     * @return SymfonyPageInterface
     */
    private function getPage(): SymfonyPageInterface
    {
        return $this->returnReasonIndexPage;
    }
}
