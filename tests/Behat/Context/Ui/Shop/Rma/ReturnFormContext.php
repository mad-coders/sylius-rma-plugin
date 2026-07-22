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
use Behat\Mink\Exception\ElementNotFoundException;
use FriendsOfBehat\PageObjectExtension\Page\UnexpectedPageException;
use Madcoders\SyliusRmaPlugin\Security\OrderReturnAuthorizerInterface;
use Sylius\Behat\Service\Setter\CookieSetterInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\ReturnFormPageInterface;
use Webmozart\Assert\Assert;

class ReturnFormContext implements Context
{
    /** @var RequestStack */
    private $requestStack;

    /** @var SessionFactoryInterface */
    private $sessionFactory;

    /** @var CookieSetterInterface */
    private $cookieSetter;

    /** @var ReturnFormPageInterface */
    private $returnFormPage;

    /** @var OrderReturnAuthorizerInterface */
    private $authorizer;

    public function __construct(
        RequestStack $requestStack,
        SessionFactoryInterface $sessionFactory,
        CookieSetterInterface $cookieSetter,
        ReturnFormPageInterface $returnFormPage,
        OrderReturnAuthorizerInterface $authorizer,
    ) {
        $this->requestStack = $requestStack;
        $this->sessionFactory = $sessionFactory;
        $this->cookieSetter = $cookieSetter;
        $this->returnFormPage = $returnFormPage;
        $this->authorizer = $authorizer;
    }

    /**
     * @Given /^I am authorize for (latest order)$/
     */
    public function iAmAuthorizeForLatestOrder(OrderInterface $order): void
    {
        $session = $this->sessionFactory->createSession();
        $request = new Request();
        $request->setSession($session);
        $this->requestStack->push($request);

        $this->authorizeThisOrder($order);

        $session->save();
        $this->cookieSetter->setCookie($session->getName(), $session->getId());
    }

    /**
     * @Given /^I am on order return page for (latest order)$/
     */
    public function iAmOnOrderReturnPage(OrderInterface $order): void
    {
        $this->returnFormPage->open(['orderNumber' => str_replace('#', '', $order->getNumber())]);
    }

    /**
     * @Then /^I should not be able to open the return form for (latest order)$/
     */
    public function iShouldNotBeAbleToOpenTheReturnForm(OrderInterface $order): void
    {
        $parameters = ['orderNumber' => str_replace('#', '', $order->getNumber())];

        try {
            // tolerant open: when an order has nothing to return the controller redirects away
            $this->returnFormPage->tryToOpen($parameters);
        } catch (UnexpectedPageException) {
            // redirected off the return form - that is the expected "nothing to return" outcome
        }

        Assert::false(
            $this->returnFormPage->isOpen($parameters),
            'Expected the return form not to open (nothing to return), but it did.',
        );
    }

    /**
     * @Then /^I should see product "([^"]+)" on the return form$/
     */
    public function iShouldSeeProductOnTheReturnForm(string $productName): void
    {
        Assert::true(
            $this->returnFormPage->hasItemWithProductName($productName),
            sprintf('Expected product "%s" to be listed on the return form, but it was not.', $productName),
        );
    }

    /**
     * @Then /^I should not see product "([^"]+)" on the return form$/
     */
    public function iShouldNotSeeProductOnTheReturnForm(string $productName): void
    {
        Assert::false(
            $this->returnFormPage->hasItemWithProductName($productName),
            sprintf('Expected product "%s" not to be listed on the return form, but it was.', $productName),
        );
    }

    /**
     * @When I choose reason with code :reasonCode
     */
    public function iChooseReason(string $reasonCode): void
    {
        try {
            $this->returnFormPage->selectReturnReason($reasonCode);
        } catch (ElementNotFoundException $e) {
        }
    }

    /**
     * @When I fill in my bank account in IBAN format
     */
    public function iFillInMyBankAccount(): void
    {
        try {
            $this->returnFormPage->fillBankAccountField();
        } catch (ElementNotFoundException $e) {
        }
    }

    /**
     * @When I fill in notes field with text :noteText
     */
    public function iFillNotes(string $noteText): void
    {
        try {
            $this->returnFormPage->fillNoteField($noteText);
        } catch (ElementNotFoundException $e) {
        }
    }

    /**
     * @When /^I choose to return (\d+) units? of the first item$/
     */
    public function iChooseToReturnUnitsOfTheFirstItem(int $qty): void
    {
        try {
            $this->returnFormPage->setItemReturnQty(0, $qty);
        } catch (ElementNotFoundException $e) {
        }
    }

    /**
     * @When /^I choose to return (\d+) units? of the second item$/
     */
    public function iChooseToReturnUnitsOfTheSecondItem(int $qty): void
    {
        try {
            $this->returnFormPage->setItemReturnQty(1, $qty);
        } catch (ElementNotFoundException $e) {
        }
    }

    /**
     * @When /^I clear the return quantity of the first item$/
     */
    public function iClearTheReturnQuantityOfTheFirstItem(): void
    {
        try {
            $this->returnFormPage->clearItemReturnQty(0);
        } catch (ElementNotFoundException $e) {
        }
    }

    /**
     * @When /^I try to return (latest order)$/
     */
    public function iTryToReturnOrder(OrderInterface $order): void
    {
        try {
            $this->returnFormPage->open(['orderNumber' => str_replace('#', '', $order->getNumber())]);
        } catch (UnexpectedPageException $e) {
            // the order is not returnable: the controller redirects away from the form
        }
    }

    /**
     * @Then /^the first item should show (\d+) returnable$/
     */
    public function theFirstItemShouldShowReturnable(int $qty): void
    {
        Assert::same($this->returnFormPage->getItemReturnQty(0), (string) $qty);
    }

    /**
     * @When I click submit button for return form
     */
    public function iSubmitReturnForm(): void
    {
        try {
            $this->returnFormPage->submitThisOrderReturnForm();
        } catch (ElementNotFoundException $e) {
        }
    }

    /**
     * @Then /^I should still be on the order return form for (latest order)$/
     */
    public function iShouldStillBeOnTheOrderReturnForm(OrderInterface $order): void
    {
        $this->returnFormPage->verify(['orderNumber' => str_replace('#', '', $order->getNumber())]);
    }

    /**
     * @When I should be redirected to order return page for order :orderNumber
     * @When /^I should be redirected to order return page for (latest order)$/
     */
    public function shouldBeOnOrderReturnPage(OrderInterface $order): void
    {
        $this->returnFormPage->verify(['orderNumber' => str_replace('#', '', $order->getNumber())]);
    }

    private function authorizeThisOrder(OrderInterface $order): void
    {
        $this->authorizer->authorize($order);
    }
}
