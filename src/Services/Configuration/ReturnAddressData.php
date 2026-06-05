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
    /** @var string */
    private $company;

    /** @var string */
    private $countryCode;

    /** @var string */
    private $street;

    /** @var string */
    private $city;

    /** @var string */
    private $postcode;

    /**
     * ReturnAddressData constructor.
     */
    public function __construct(
        string $company,
        string $countryCode,
        string $street,
        string $city,
        string $postcode,
    ) {
        $this->company = $company;
        $this->countryCode = $countryCode;
        $this->street = $street;
        $this->city = $city;
        $this->postcode = $postcode;
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
