<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Shop\Rma;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Shop\FlashNotificationContextTrait;
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
        // TODO: Implement getSubmitButton() method.
    }

    public function getRouteName(): string
    {
        return 'madcoders_rma_verification';
    }
}
