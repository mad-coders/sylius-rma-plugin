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
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnReason\CreatePageInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnReason\IndexPageInterface;
use Webmozart\Assert\Assert;

class ReturnReasonContext implements Context
{
    /** @var IndexPageInterface */
    private $returnReasonIndexPage;

    /** @var CreatePageInterface */
    private $returnReasonCreatePage;

    /** @var SharedStorageInterface */
    private $sharedStorage;

    /**
     * ReturnReasonContext constructor.
     *
     * @param IndexPageInterface $returnReasonIndexPage
     */
    public function __construct(
        IndexPageInterface $returnReasonIndexPage,
        CreatePageInterface $returnReasonCreatePage,
        SharedStorageInterface $sharedStorage
    )
    {
        $this->returnReasonIndexPage = $returnReasonIndexPage;
        $this->returnReasonCreatePage = $returnReasonCreatePage;
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
        foreach($table as $row) {
            $fieldLocator = $row['field'];
            $this->returnReasonCreatePage->choosesFormElement($row['value'], $fieldLocator);
        }
    }

    /**
     * @When I click submit button
     */
    public function iClickSubmitButton()
    {
        $this->returnReasonCreatePage->create();
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
