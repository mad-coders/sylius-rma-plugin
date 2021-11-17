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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\AddressConfiguration;

use Behat\Mink\Exception\ElementNotFoundException;
use Sylius\Behat\Page\Admin\Crud\UpdatePageInterface as BaseUpdatePageInterface;

interface UpdatePageInterface extends BaseUpdatePageInterface
{
    /**
     * @throws ElementNotFoundException
     */
    public function choosesFormElement(string $name, string $element): void;

    public function fillFormField(array $row): void;

    public function selectCountry(string $countryName): void;

    public function selectChannel(string $channelName): void;

    public function hasReturnAddress(
        string $company,
        string $street,
        string $postcode,
        string $city,
        string $countryName
    ): bool;
}
