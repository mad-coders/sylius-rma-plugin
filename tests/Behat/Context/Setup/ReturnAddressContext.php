<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfiguration;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Intl;
use Webmozart\Assert\Assert;

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
class ReturnAddressContext implements Context
{
    /** @var RepositoryInterface */
    private $channelRepository;

    /** @var RepositoryInterface */
    private $rmaConfigurationRepository;

    /**
     * ReturnAddressContext constructor
     *
     * @param RepositoryInterface $channelRepository
     * @param RepositoryInterface $rmaConfigurationRepository
     */
    public function __construct(
        RepositoryInterface $channelRepository,
        RepositoryInterface $rmaConfigurationRepository
    )
    {
        $this->channelRepository = $channelRepository;
        $this->rmaConfigurationRepository = $rmaConfigurationRepository;
    }

    /**
     * @Given Store return address with data for channel :channelName:
     *
     * @throws \Exception
     */
    public function storeHasReturnAddressForChannel(string $channelName, TableNode $table): void
    {
        $channel = $this->findChannelByName($channelName);
        $addressData = $this->createAddressData($table);

        $rmaConfiguration = new RmaConfiguration();
        $rmaConfiguration->setChannel($channel);
        $rmaConfiguration->setParameter('address');
        $rmaConfiguration->setValue(json_encode($addressData));

        $this->rmaConfigurationRepository->add($rmaConfiguration);
    }

    /**
     * @throws \Exception
     */
    private function findChannelByName(string $channelName): ChannelInterface
    {
        if (!$channels = $this->channelRepository->findByName($channelName)) {
            throw new \Exception(sprintf('Could not find a channels by Name "%s"', $channelName));
        }

        if (count($channels) > 1) {
            throw new \Exception(sprintf('Find more channels by Name "%s"', $channelName));
        }

        if (!$channels[0] instanceof ChannelInterface) {
            throw new \InvalidArgumentException(sprintf('Channel "%s" does not support', get_class($channels[0])));
        }

        return $channels[0];
    }

    private function createAddressData(TableNode $table): array
    {
        $addressData = [];
        foreach($table as $row) {
            if ($row['field'] === 'country') {
                $addressData['countryCode'] =  $this->getCountryCodeByName($row['value']);
            } else {
                $addressData[$row['field']] = $row['value'];
            }
        }

        return $addressData;
    }

    private function getCountryCodeByName(string $countryName): string
    {
        $countryList = array_flip(Countries::getNames());
        Assert::keyExists(
            $countryList,
            $countryName,
            sprintf('The country with name "%s" not found', $countryName)
        );

        return $countryList[$countryName];
    }
}
