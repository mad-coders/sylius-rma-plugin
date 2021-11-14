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
use Sylius\Component\Resource\Model\ResourceInterface as ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class OrderReturn implements OrderReturnInterface, ResourceInterface, TimestampableInterface
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
    private $customerNumber;

    /**
     * @var string
     */
    private $returnNumber;

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
    private $firstname;

    /**
     * @var string|null
     */
    private $lastname;

    /**
     * @var string|null
     */
    private $phoneNumber;

    /**
     * @var string|null
     */
    private $customerEmail;

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
    private $orderReturnStatus = self::STATUS_DRAFT;

    /**
     * @var string
     */
    private $customerIp;

    /**
     * @var string|null
     */
    private $customerNote;

    /**
     * @var array
     */
    private $orderReturnConsents = [];

    /** @var string|null */
    private $bankAccountNumber;

    /** @var OrderReturnItem[] */
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
    public function getCustomerNumber(): string
    {
        return $this->customerNumber;
    }

    /**
     * @param string $customerNumber
     */
    public function setCustomerNumber(string $customerNumber): void
    {
        $this->customerNumber = $customerNumber;
    }

    /**
     * @return string
     */
    public function getReturnNumber(): string
    {
        return $this->returnNumber;
    }

    /**
     * @param string $returnNumber
     */
    public function setReturnNumber(string $returnNumber): void
    {
        $this->returnNumber = $returnNumber;
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
     * @return string|null
     */
    public function getReturnReason(): ?string
    {
        return $this->returnReason;
    }

    /**
     * @param string|null $returnReason
     */
    public function setReturnReason(?string $returnReason): void
    {
        $this->returnReason = $returnReason;
    }

    /**
     * @return array
     */
    public function getOrderReturnConsents(): array
    {
        return $this->orderReturnConsents;
    }

    /**
     * @param array $orderReturnConsents
     */
    public function setOrderReturnConsents(array $orderReturnConsents): void
    {
        $this->orderReturnConsents = $orderReturnConsents;
    }

    /**
     * @param array $orderReturnConsent
     */
    public function addOrderReturnConsent(array $orderReturnConsent): void
    {
        $this->orderReturnConsents[] = $orderReturnConsent;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $firstname
     */
    public function setFirstName(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string|null $lastname
     */
    public function setLastName(?string $lastname): void
    {
        $this->lastname = $lastname;
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
    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    /**
     * @param string|null $customerEmail
     */
    public function setCustomerEmail(?string $customerEmail): void
    {
        $this->customerEmail = $customerEmail;
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
     * @return string|null
     */
    public function getCustomerNote(): ?string
    {
        return $this->customerNote;
    }

    /**
     * @param string|null $customerNote
     */
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

    /**
     * @param OrderReturnItemInterface $item
     */
    public function addItem(OrderReturnItemInterface $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrderReturn($this);
        }
    }

    /**
     * @param OrderReturnItemInterface $item
     */
    public function removeItem(OrderReturnItemInterface $item): void
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
        }
    }

    /**
     * @return string|null
     */
    public function getBankAccountNumber(): ?string
    {
        return $this->bankAccountNumber;
    }

    /**
     * @param string|null $bankAccountNumber
     */
    public function setBankAccountNumber(?string $bankAccountNumber): void
    {
        $this->bankAccountNumber = $bankAccountNumber;
    }

}
