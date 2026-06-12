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

use Sylius\Component\Resource\Model\ResourceInterface;

interface OrderReturnInterface extends ResourceInterface
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_NEW = 'new';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_WITHDRAWAL_REQUEST = 'withdrawal_request';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const TRANSITION_NEW = 'new';

    public const TRANSITION_COMPLETE = 'complete';

    public const TRANSITION_CANCEL = 'cancel';

    public const TRANSITION_REQUEST_WITHDRAWAL = 'request_withdrawal';

    public const TRANSITION_WITHDRAW = 'withdraw';

    public const TRANSITION_FALLBACK_TO_RETURN = 'fallback_to_return';

    public const GRAPH = 'return_status';

    public const STATUS_LIST = [
        self::STATUS_DRAFT,
        self::STATUS_NEW,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELED,
        self::STATUS_WITHDRAWAL_REQUEST,
        self::STATUS_WITHDRAWN,
    ];

    public function getId(): ?int;

    public function getOrderNumber(): string;

    public function setOrderNumber(string $orderNumber): void;

    public function getCustomerNumber(): string;

    public function setCustomerNumber(string $customerNumber): void;

    public function getReturnNumber(): string;

    public function getCustomerEmail(): ?string;

    public function setCustomerEmail(?string $customerEmail): void;

    public function setReturnNumber(string $returnNumber): void;

    public function getChannelCode(): string;

    public function setChannelCode(string $channelCode): void;

    public function getReturnReason(): ?string;

    public function setReturnReason(?string $returnReason): void;

    public function getOrderReturnConsents(): array;

    public function setOrderReturnConsents(array $orderReturnConsents): void;

    public function addOrderReturnConsent(array $orderReturnConsent): void;

    public function getFirstName(): ?string;

    public function setFirstName(?string $firstname): void;

    public function getLastName(): ?string;

    public function setLastName(?string $lastname): void;

    public function getPhoneNumber(): ?string;

    public function setPhoneNumber(?string $phoneNumber): void;

    public function getCompany(): ?string;

    public function setCompany(?string $company): void;

    public function getCountryCode(): ?string;

    public function setCountryCode(?string $countryCode): void;

    public function getProvinceCode(): ?string;

    public function setProvinceCode(?string $provinceCode): void;

    public function getProvinceName(): ?string;

    public function setProvinceName(?string $provinceName): void;

    public function getStreet(): ?string;

    public function setStreet(?string $street): void;

    public function getCity(): ?string;

    public function setCity(?string $city): void;

    public function getPostcode(): ?string;

    public function setPostcode(?string $postcode): void;

    public function getOrderReturnStatus(): string;

    public function setOrderReturnStatus(string $orderReturnStatus): void;

    public function getCustomerIp(): string;

    public function setCustomerIp(string $customerIp): void;

    /**
     * @return OrderReturnItemInterface[]
     */
    public function getItems(): iterable;

    public function addItem(OrderReturnItemInterface $item): void;

    public function removeItem(OrderReturnItemInterface $item): void;
}
