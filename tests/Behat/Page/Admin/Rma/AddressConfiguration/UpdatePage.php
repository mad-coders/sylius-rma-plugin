<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\AddressConfiguration;

use Sylius\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Behaviour\ChoosesFormElement;
use Behat\Mink\Exception\ElementNotFoundException;

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
class UpdatePage extends BaseUpdatePage implements UpdatePageInterface
{
    use ChoosesFormElement;

    /**
     * @throws ElementNotFoundException
     */
    public function getSelectedChannelId(): string
    {
        return $channelName = $this->getElement('rma-configuration-channel-field')->getValue();;
    }

    public function fillFormField(array $row): void
    {
        try {
            $this->getElement($row['field'])->setValue($row['value']);
        } catch (ElementNotFoundException $e) {
        }
    }

    public function selectCountry(string $countryName): void
    {
        $this->getElement('country')->selectOption($countryName);
    }

    public function selectChannel(string $channelName): void
    {
        $this->getElement('rma-configuration-channel-field')->selectOption($channelName);
    }

    public function changeChannel(): void
    {
        $this->getDocument()->pressButton('sylius_change_channel_button');
    }

    public function hasReturnAddress(
        string $company,
        string $street,
        string $postcode,
        string $city,
        string $countryName
    ): bool {
        $itsCompany = $this->getElement('company')->getValue();
        $itsStreet = $this->getElement('street')->getValue();
        $itsCity = $this->getElement('city')->getValue();
        $itsPostcode = $this->getElement('postcode')->getValue();
        $itsCountry = $this->getElement('country')->getText();

        $addressText = $itsCompany . ' ' . $itsStreet . ' ' . $itsCity . ' ' . $itsPostcode . ' ' . $itsCountry;

        return $this->hasAddress($addressText, $company, $street, $postcode, $city, $countryName);
    }

    private function hasAddress(
        string $elementText,
        string $company,
        string $street,
        string $postcode,
        string $city,
        string $countryName
    ): bool {
        return
            (stripos($elementText, $company) !== false) &&
            (stripos($elementText, $street) !== false) &&
            (stripos($elementText, $city) !== false) &&
            (stripos($elementText, $postcode) !== false) &&
            (stripos($elementText, $countryName) !== false)
            ;
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'rma-configuration-channel-field' => '#madcoders_rma_admin_choice_channel_channelChoice',
            'country' => '#madcoders_rma_config_address_to_channel_countryCode',
            'company' => '#madcoders_rma_config_address_to_channel_company',
            'street' => '#madcoders_rma_config_address_to_channel_street',
            'city' => '#madcoders_rma_config_address_to_channel_city',
            'postcode' => '#madcoders_rma_config_address_to_channel_postcode',
        ]);
    }
}
