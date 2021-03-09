<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Entity;

interface OrderReturnInterface
{
    const STATUS_DRAFT = 'draft';
    const STATUS_NEW = 'new';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    const TRANSITION_NEW = 'new';
    const TRANSITION_ACCEPT = 'accept';
    const TRANSITION_REJECT = 'reject';

    const GRAPH = 'return_status';

    const STATUS_LIST = [
        self::STATUS_DRAFT,
        self::STATUS_NEW,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
    ];

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @return string
     */
    public function getOrderNumber(): string;

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber): void;

    /**
     * @return string
     */
    public function getReturnNumber(): string;

    /**
     * @param string $returnNumber
     */
    public function setReturnNumber(string $returnNumber): void;

    /**
     * @return string
     */
    public function getChannelCode(): string;

    /**
     * @param string $channelCode
     */
    public function setChannelCode(string $channelCode): void;

    /**
     * @return string|null
     */
    public function getReturnReason(): ?string;

    /**
     * @param string|null $returnReason
     */
    public function setReturnReason(?string $returnReason): void;

    /**
     * @return bool
     */
    public function getOrderReturnConsent(): bool;

    /**
     * @param bool $orderReturnConsent
     */
    public function setOrderReturnConsent(bool $orderReturnConsent): void;

    /**
     * @return string|null
     */
    public function getOrderReturnConsentLabel(): ?string;

    /**
     * @param string|null $orderReturnConsentLabel
     */
    public function setOrderReturnConsentLabel(?string $orderReturnConsentLabel): void;

    /**
     * @return string|null
     */
    public function getFirstName(): ?string;

    /**
     * @param string|null $firstname
     */
    public function setFirstName(?string $firstname): void;

    /**
     * @return string|null
     */
    public function getLastName(): ?string;

    /**
     * @param string|null $lastname
     */
    public function setLastName(?string $lastname): void;

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string;

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void;

    /**
     * @return string|null
     */
    public function getCompany(): ?string;

    /**
     * @param string|null $company
     */
    public function setCompany(?string $company): void;

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string;

    /**
     * @param string|null $countryCode
     */
    public function setCountryCode(?string $countryCode): void;

    /**
     * @return string|null
     */
    public function getProvinceCode(): ?string;

    /**
     * @param string|null $provinceCode
     */
    public function setProvinceCode(?string $provinceCode): void;

    /**
     * @return string|null
     */
    public function getProvinceName(): ?string;

    /**
     * @param string|null $provinceName
     */
    public function setProvinceName(?string $provinceName): void;

    /**
     * @return string|null
     */
    public function getStreet(): ?string;

    /**
     * @param string|null $street
     */
    public function setStreet(?string $street): void;

    /**
     * @return string|null
     */
    public function getCity(): ?string;

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void;

    /**
     * @return string|null
     */
    public function getPostcode(): ?string;

    /**
     * @param string|null $postcode
     */
    public function setPostcode(?string $postcode): void;

    /**
     * @return string
     */
    public function getOrderReturnStatus(): string;

    /**
     * @param string $orderReturnStatus
     */
    public function setOrderReturnStatus(string $orderReturnStatus): void;

    /**
     * @return string
     */
    public function getCustomerIp(): string;

    /**
     * @param string $customerIp
     */
    public function setCustomerIp(string $customerIp): void;

    /**
     * @return OrderReturnItemInterface[]
     */
    public function getItems(): iterable;

    /**
     * @param OrderReturnItemInterface $item
     */
    public function addItem(OrderReturnItemInterface $item): void;

    /**
     * @param OrderReturnItemInterface $item
     */
    public function removeItem(OrderReturnItemInterface $item): void;
}
