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

class ReturnAddressData
{
    /**
     * ReturnAddressData constructor.
     */
    public function __construct(
        private readonly string $company,
        private readonly string $countryCode,
        private readonly string $street,
        private readonly string $city,
        private readonly string $postcode,
    ) {
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostcode(): string
    {
        return $this->postcode;
    }
}
