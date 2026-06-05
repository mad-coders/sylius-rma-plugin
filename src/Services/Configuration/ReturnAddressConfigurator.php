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

namespace Madcoders\SyliusRmaPlugin\Services\Configuration;

use Exception;
use Madcoders\SyliusRmaPlugin\Entity\RmaConfigurationInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

class ReturnAddressConfigurator
{
    /**
     * @param RepositoryInterface<RmaConfigurationInterface> $configurationRepository
     */
    public function __construct(private readonly RepositoryInterface $configurationRepository)
    {
    }

    public function getReturnAddressForReturnForm(ChannelInterface $channel): ReturnAddressData
    {
        $addressConfigByChannel = $this->configurationRepository
            ->findOneBy(['channel' => $channel, 'parameter' => 'address']);

        if (!$addressConfigByChannel instanceof RmaConfigurationInterface) {
            throw new Exception('Address not defined for Selected channel');
        }

        $addressData = json_decode((string) $addressConfigByChannel->getValue(), true);
        if (!is_array($addressData)) {
            throw new Exception('Address not defined for Selected channel');
        }

        Assert::string($addressData['company']);
        Assert::string($addressData['countryCode']);
        Assert::string($addressData['street']);
        Assert::string($addressData['city']);
        Assert::string($addressData['postcode']);

        return new ReturnAddressData(
            $addressData['company'],
            $addressData['countryCode'],
            $addressData['street'],
            $addressData['city'],
            $addressData['postcode'],
        );
    }
}
