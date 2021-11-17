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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Ui\Admin\Rma;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\AddressConfiguration\UpdatePageInterface;
use Webmozart\Assert\Assert;

class AddressConfigurationContext implements Context
{
    /** @var UpdatePageInterface */
    private $returnConsentUpdatePage;
    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepository;


    /**
     * AddressConfigurationContext constructor
     *
     * @param UpdatePageInterface $returnConsentUpdatePage
     */
    public function __construct(
        UpdatePageInterface $returnConsentUpdatePage,
        ChannelRepositoryInterface $channelRepository
    )
    {
        $this->returnConsentUpdatePage = $returnConsentUpdatePage;
        $this->channelRepository = $channelRepository;
    }

    /**
     * @Given I am on configuration page
     */
    public function iAmOnConfigurationPage(): void
    {
        $this->returnConsentUpdatePage->open();
    }

    /**
     * @Given configuration form load for a channel named :channelName
     */
    public function isConfigurationFormForSelectedChanel(string $channelName): void
    {
        $channelId = $this->returnConsentUpdatePage->getSelectedChannelId();
        $channel = $this->channelRepository->findOneBy(['id' => $channelId]);

        Assert::true(stripos($channelName,$channel->getName()) !== false);
    }

    /**
     * @When I fill store return address data:
     */
    public function fillStoreReturnAddressDataForSelectedChannel(TableNode $table): void
    {
        foreach($table as $row) {
            if ($row['type'] == 'field') {
                $this->returnConsentUpdatePage->fillFormField($row);
            } elseif ($row['type'] == 'select') {
                $this->returnConsentUpdatePage->selectCountry($row['value']);
            }
        }
    }

    /**
     * @When I select channel when name :channelName
     */
    public function selectChannelWithName(string $channelName)
    {
        $this->returnConsentUpdatePage->selectChannel($channelName);
    }

    /**
     * @When I click change channel button
     */
    public function iClickChangeChannelButton()
    {
        $this->returnConsentUpdatePage->changeChannel();
    }

    /**
     * @When I click continue button
     */
    public function iClickContinueButton()
    {
        $this->returnConsentUpdatePage->saveChanges();
    }

    /**
     * @Then I should see :company, :street, :postcode, :city, :countryName as return address information
     */
    public function iShouldSeeAsReturnAddress($company, $street, $postcode, $city, $countryName)
    {
        Assert::true($this->returnConsentUpdatePage->hasReturnAddress($company, $street, $postcode, $city, $countryName));
    }
}
