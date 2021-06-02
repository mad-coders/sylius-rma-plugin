<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\FlashNotificationInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\FlashNotificationTrait;

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
class AuthPage extends SymfonyPage implements AuthPageInterface, FlashNotificationInterface
{
    use FlashNotificationTrait;

    public function getSubmitButton(): NodeElement
    {
        return $this->getElement('rma-continue-button');
    }

    public function clickSubmitButton(): void
    {
        $this->getSubmitButton()->click();
    }

    public function getAuthCodeFieled(): NodeElement
    {
        return $this->getElement('rma-auth-code-field');
    }

    public function insertAuthCode(string $authCode): void
    {
        $this->getAuthCodeFieled()->setValue($authCode);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'rma-auth-code-field' => '[data-test-rma-auth-code-field]',
            'rma-continue-button' => '[data-test-rma-continue-button]',
        ]);
    }


    public function getRouteName(): string
    {
        return 'madcoders_rma_verification';
    }
}
