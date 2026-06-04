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

namespace Madcoders\SyliusRmaPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class OrderReturn implements OrderReturnInterface, TimestampableInterface
{
    use TimestampableTrait;

    /** @var int */
    private $id;

    /** @var string */
    private $orderNumber;

    /** @var string */
    private $customerNumber;

    /** @var string */
    private $returnNumber;

    /** @var string */
    private $channelCode;

    /** @var string */
    private $returnReason;

    /** @var string|null */
    private $firstname;

    /** @var string|null */
    private $lastname;

    /** @var string|null */
    private $phoneNumber;

    /** @var string|null */
    private $customerEmail;

    /** @var string|null */
    private $company;

    /** @var string|null */
    private $countryCode;

    /** @var string|null */
    private $provinceCode;

    /** @var string|null */
    private $provinceName;

    /** @var string|null */
    private $street;

    /** @var string|null */
    private $city;

    /** @var string|null */
    private $postcode;

    /** @var string */
    private $orderReturnStatus = self::STATUS_DRAFT;

    /** @var string */
    private $customerIp;

    /** @var string|null */
    private $customerNote;

    /** @var array */
    private $orderReturnConsents = [];

    /** @var string|null */
    private $bankAccountNumber;

    /** @var OrderReturnItem[] */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getCustomerNumber(): string
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(string $customerNumber): void
    {
        $this->customerNumber = $customerNumber;
    }

    public function getReturnNumber(): string
    {
        return $this->returnNumber;
    }

    public function setReturnNumber(string $returnNumber): void
    {
        $this->returnNumber = $returnNumber;
    }

    public function getChannelCode(): string
    {
        return $this->channelCode;
    }

    public function setChannelCode(string $channelCode): void
    {
        $this->channelCode = $channelCode;
    }

    public function getReturnReason(): ?string
    {
        return $this->returnReason;
    }

    public function setReturnReason(?string $returnReason): void
    {
        $this->returnReason = $returnReason;
    }

    public function getOrderReturnConsents(): array
    {
        return $this->orderReturnConsents;
    }

    public function setOrderReturnConsents(array $orderReturnConsents): void
    {
        $this->orderReturnConsents = $orderReturnConsents;
    }

    public function addOrderReturnConsent(array $orderReturnConsent): void
    {
        $this->orderReturnConsents[] = $orderReturnConsent;
    }

    public function getFirstName(): ?string
    {
        return $this->firstname;
    }

    public function setFirstName(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastName(): ?string
    {
        return $this->lastname;
    }

    public function setLastName(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(?string $customerEmail): void
    {
        $this->customerEmail = $customerEmail;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getProvinceCode(): ?string
    {
        return $this->provinceCode;
    }

    public function setProvinceCode(?string $provinceCode): void
    {
        $this->provinceCode = $provinceCode;
    }

    public function getProvinceName(): ?string
    {
        return $this->provinceName;
    }

    public function setProvinceName(?string $provinceName): void
    {
        $this->provinceName = $provinceName;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): void
    {
        $this->postcode = $postcode;
    }

    public function getOrderReturnStatus(): string
    {
        return $this->orderReturnStatus;
    }

    public function setOrderReturnStatus(string $orderReturnStatus): void
    {
        $this->orderReturnStatus = $orderReturnStatus;
    }

    public function getCustomerIp(): string
    {
        return $this->customerIp;
    }

    public function setCustomerIp(string $customerIp): void
    {
        $this->customerIp = $customerIp;
    }

    public function getCustomerNote(): ?string
    {
        return $this->customerNote;
    }

    public function setCustomerNote(?string $customerNote): void
    {
        $this->customerNote = $customerNote;
    }

    /**
     * @return OrderReturnItemInterface[]
     */
    public function getItems(): iterable
    {
        return $this->items;
    }

    public function addItem(OrderReturnItemInterface $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrderReturn($this);
        }
    }

    public function removeItem(OrderReturnItemInterface $item): void
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }
    }

    public function getBankAccountNumber(): ?string
    {
        return $this->bankAccountNumber;
    }

    public function setBankAccountNumber(?string $bankAccountNumber): void
    {
        $this->bankAccountNumber = $bankAccountNumber;
    }
}
