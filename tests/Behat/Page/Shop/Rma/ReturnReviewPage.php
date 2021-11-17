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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use Behat\Mink\Exception\ElementNotFoundException;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\FlashNotificationInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\FlashNotificationTrait;

class ReturnReviewPage extends SymfonyPage implements ReturnReviewPageInterface, FlashNotificationInterface
{
    use FlashNotificationTrait;

    public function getRouteName(): string
    {
        return 'madcoders_rma_return_form_accept';
    }

    /**
     * @throws ElementNotFoundException
     */
    public function approveThisOrderReturnForm(): void
    {
        $this->getElement('rma_approve_return_form')->click();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'rma_approve_return_form' => '[data-test-madcoders-rma-approve-return-form-button]'
        ]);
    }
}
