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

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface RmaConfigurationInterface extends ResourceInterface
{
    public function getId(): int;

    public function getParameter(): ?string;

    public function setParameter(?string $parameter): void;

    public function getValue(): ?string;

    public function setValue(?string $value): void;

    public function getChannel(): ChannelInterface;

    public function setChannel(ChannelInterface $channel): void;
}
