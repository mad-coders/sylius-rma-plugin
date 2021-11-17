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
use Sylius\Component\Resource\Model\ResourceInterface as ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class RmaConfiguration implements RmaConfigurationInterface, ResourceInterface, TimestampableInterface
{
    use TimestampableTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $parameter;

    /** @var string|null */
    private $value;

    /** @var ChannelInterface */
    private $channel;

    /** @return int */
    public function getId(): int
    {
        return $this->id;
    }

    /** @return string|null */
    public function getParameter(): ?string
    {
        return $this->parameter;
    }

    /** @param string|null $parameter */
    public function setParameter(?string $parameter): void
    {
        $this->parameter = $parameter;
    }

    /** @return string|null */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /** @param string|null $value */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /** @return ChannelInterface */
    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }

    /** @param ChannelInterface $channel */
    public function setChannel(ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }
}
