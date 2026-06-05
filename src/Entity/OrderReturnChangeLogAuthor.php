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

use Sylius\Component\Resource\Model\ResourceInterface as ResourceInterface;

class OrderReturnChangeLogAuthor implements OrderReturnChangeLogAuthorInterface, ResourceInterface
{
    private int $id = 0;

    private ?string $type = null;

    private ?string $firstName = null;

    private ?string $lastName = null;

    private OrderReturnChangeLog $changeLog;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type ?? '';
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getFirstName(): string
    {
        return $this->firstName ?? '';
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName ?? '';
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getChangeLog(): OrderReturnChangeLog
    {
        return $this->changeLog;
    }

    public function setChangeLog(OrderReturnChangeLog $changeLog): void
    {
        $this->changeLog = $changeLog;
    }
}
