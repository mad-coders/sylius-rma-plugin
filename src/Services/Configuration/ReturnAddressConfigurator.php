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

use Madcoders\SyliusRmaPlugin\Entity\RmaConfigurationInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Exception;

class ReturnAddressConfigurator
{
    /** @var RepositoryInterface */
    private $configurationRepository;

    /**
     * ReturnAddressConfigurator constructor.
     * @param RepositoryInterface $configurationRepository
     */
    public function __construct(RepositoryInterface $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function getReturnAddressForReturnForm(ChannelInterface $channel): ReturnAddressData {
        /** @var RmaConfigurationInterface|null $addressConfigByChannel */
        $addressConfigByChannel = $this->configurationRepository
            ->findOneBy(['channel' => $channel, 'parameter' => 'address']);

        if (!$addressConfigByChannel instanceof RmaConfigurationInterface) {
            throw new Exception('Address not defined for Selected channel');
        }

        if (!$addressData = json_decode($addressConfigByChannel->getValue(), true)) {
            throw new Exception('Address not defined for Selected channel');
        }
        return new ReturnAddressData(
            $addressData['company'],
            $addressData['countryCode'],
            $addressData['street'],
            $addressData['city'],
            $addressData['postcode']
        );
    }
}
