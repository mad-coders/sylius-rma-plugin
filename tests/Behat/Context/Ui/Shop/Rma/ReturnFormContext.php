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
use Madcoders\SyliusRmaPlugin\Security\OrderReturnAuthorizerInterface;
use Sylius\Behat\Service\Setter\CookieSetterInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma\ReturnFormPageInterface;

class ReturnFormContext implements Context
{
    /** @var SessionInterface */
    private $session;

    /** @var CookieSetterInterface */
    private $cookieSetter;

    /** @var ReturnFormPageInterface */
    private $returnFormPage;

    /** @var OrderReturnAuthorizerInterface */
    private $authorizer;

    public function __construct(
        SessionInterface $session,
        CookieSetterInterface $cookieSetter,
        ReturnFormPageInterface $returnFormPage,
        OrderReturnAuthorizerInterface $authorizer
    )
    {
        $this->session = $session;
        $this->cookieSetter = $cookieSetter;
        $this->returnFormPage = $returnFormPage;
        $this->authorizer = $authorizer;
    }

    /**
     * @Given /^I am authorize for (latest order)$/
     */
    public function iAmAuthorizeForLatestOrder(OrderInterface $order): void
    {
        $this->authorizeThisOrder($order);
        $this->session->save();
        $this->cookieSetter->setCookie($this->session->getName(), $this->session->getId());
    }

    /**
     * @Given /^I am on order return page for (latest order)$/
     */
    public function iAmOnOrderReturnPage(OrderInterface $order): void
    {
        $this->returnFormPage->open(['orderNumber' => str_replace('#', '', $order->getNumber())]);
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
