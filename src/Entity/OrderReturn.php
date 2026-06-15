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
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Webmozart\Assert\Assert;

class OrderReturn implements OrderReturnInterface, TimestampableInterface
{
    use TimestampableTrait;

    /** @var int */
    private $id;

    private string $orderNumber;

    private string $customerNumber;

    private string $returnNumber;

    private string $channelCode;

    private ?string $returnReason = null;

    private ?string $firstname = null;

    private ?string $lastname = null;

    private ?string $phoneNumber = null;

    private ?string $customerEmail = null;

    private ?string $company = null;

    private ?string $countryCode = null;

    private ?string $provinceCode = null;

    private ?string $provinceName = null;

    private ?string $street = null;

    private ?string $city = null;

    private ?string $postcode = null;

    private string $orderReturnStatus = self::STATUS_DRAFT;

    private ?string $customerIp = null;

    private ?string $customerNote = null;

    private array $orderReturnConsents = [];

    private ?string $bankAccountNumber = null;

    private ?string $accountHolderName = null;

    private ?string $bankName = null;

    /** @var Collection<int, OrderReturnItem> */
    private Collection $items;

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
        return $this->customerIp ?? '';
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
        Assert::isInstanceOf($item, OrderReturnItem::class);
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrderReturn($this);
        }
    }

    public function removeItem(OrderReturnItemInterface $item): void
    {
        Assert::isInstanceOf($item, OrderReturnItem::class);
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

    public function getAccountHolderName(): ?string
    {
        return $this->accountHolderName;
    }

    public function setAccountHolderName(?string $accountHolderName): void
    {
        $this->accountHolderName = $accountHolderName;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): void
    {
        $this->bankName = $bankName;
    }
}
