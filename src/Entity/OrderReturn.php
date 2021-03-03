<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Resource\Model\ResourceInterface as ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class OrderReturn implements ResourceInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var string
     */
    private $channelCode;

    /**
     * @var string
     */
    private $returnReason;

    /**
     * @var string|null
     */
    private $firstName;

    /**
     * @var string|null
     */
    private $lastName;

    /**
     * @var string|null
     */
    private $phoneNumber;

    /**
     * @var string|null
     */
    private $company;

    /**
     * @var string|null
     */
    private $countryCode;

    /**
     * @var string|null
     */
    private $provinceCode;

    /**
     * @var string|null
     */
    private $provinceName;

    /**
     * @var string|null
     */
    private $street;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var string|null
     */
    private $postcode;

    /**
     * @var string
     */
    private $orderReturnStatus;

    /**
     * @var string
     */
    private $customerIp;

    /**
     * @var int
     */
    private $orderReturnConsent;

    /**
     * @var string
     */
    private $orderReturnConsentLabel;

    /**
     * @var OrderReturnItem[]
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getChannelCode(): string
    {
        return $this->channelCode;
    }

    /**
     * @param string $channelCode
     */
    public function setChannelCode(string $channelCode): void
    {
        $this->channelCode = $channelCode;
    }

    /**
     * @return string
     */
    public function getReturnReason(): string
    {
        return $this->returnReason;
    }

    /**
     * @param string $returnReason
     */
    public function setReturnReason(string $returnReason): void
    {
        $this->returnReason = $returnReason;
    }

    /**
     * @return int
     */
    public function getOrderReturnConsent(): int
    {
        return $this->orderReturnConsent;
    }

    /**
     * @param int $orderReturnConsent
     */
    public function setOrderReturnConsent(int $orderReturnConsent): void
    {
        $this->orderReturnConsent = $orderReturnConsent;
    }

    /**
     * @return string
     */
    public function getOrderReturnConsentLabel(): string
    {
        return $this->orderReturnConsentLabel;
    }

    /**
     * @param string $orderReturnConsentLabel
     */
    public function setOrderReturnConsentLabel(string $orderReturnConsentLabel): void
    {
        $this->orderReturnConsentLabel = $orderReturnConsentLabel;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param string|null $company
     */
    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @param string|null $countryCode
     */
    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string|null
     */
    public function getProvinceCode(): ?string
    {
        return $this->provinceCode;
    }

    /**
     * @param string|null $provinceCode
     */
    public function setProvinceCode(?string $provinceCode): void
    {
        $this->provinceCode = $provinceCode;
    }

    /**
     * @return string|null
     */
    public function getProvinceName(): ?string
    {
        return $this->provinceName;
    }

    /**
     * @param string|null $provinceName
     */
    public function setProvinceName(?string $provinceName): void
    {
        $this->provinceName = $provinceName;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     */
    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * @param string|null $postcode
     */
    public function setPostcode(?string $postcode): void
    {
        $this->postcode = $postcode;
    }

    /**
     * @return string
     */
    public function getOrderReturnStatus(): string
    {
        return $this->orderReturnStatus;
    }

    /**
     * @param string $orderReturnStatus
     */
    public function setOrderReturnStatus(string $orderReturnStatus): void
    {
        $this->orderReturnStatus = $orderReturnStatus;
    }

    /**
     * @return string
     */
    public function getCustomerIp(): string
    {
        return $this->customerIp;
    }

    /**
     * @param string $customerIp
     */
    public function setCustomerIp(string $customerIp): void
    {
        $this->customerIp = $customerIp;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return OrderReturnItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param OrderReturnItem[] $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }

}
