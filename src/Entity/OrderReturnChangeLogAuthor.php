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
    /** @var int */
    private $id;

    /** @var string */
    private $type;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var OrderReturnChangeLog */
    private $changeLog;

    /** @return int */
    public function getId(): int
    {
        return $this->id;
    }

    /** @param int $id */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /** @return string */
    public function getType(): string
    {
        return $this->type;
    }

    /** @param string $type */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**  @return string */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /** @param string $firstName */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /** @return string */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /** @param string $lastName */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /** @return OrderReturnChangeLog */
    public function getChangeLog(): OrderReturnChangeLog
    {
        return $this->changeLog;
    }

    /** @param OrderReturnChangeLog $changeLog */
    public function setChangeLog(OrderReturnChangeLog $changeLog): void
    {
        $this->changeLog = $changeLog;
    }


}
