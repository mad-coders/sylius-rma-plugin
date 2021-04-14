<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Piotr Lewandowski
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Entity;

use Sylius\Component\Core\Model\ChannelInterface;

interface RmaConfigurationInterface
{
    public function getId(): int;

    public function getParameter(): ?string;

    public function setParameter(?string $parameter): void;

    public function getValue(): ?string;

    public function setValue(?string $value): void;

    public function getChannel(): ChannelInterface;

    public function setChannel(ChannelInterface $channel): void;
}
