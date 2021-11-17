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

use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\SlugAwareInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;
use Sylius\Component\Resource\Model\ToggleableInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslationInterface;

interface OrderReturnReasonInterface extends
    CodeAwareInterface,
    TranslatableInterface,
    ResourceInterface,
    TimestampableInterface,
    SlugAwareInterface,
    ToggleableInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getPosition(): ?int;

    public function setPosition(?int $position): void;

    public function getDeadlineToReturn(): ?int;

    public function setDeadlineToReturn(?int $deadlineToReturn): void;

    /**
     * @param string|null $locale
     * @return OrderReturnReasonTranslationInterface
     */
    public function getTranslation(?string $locale = null): TranslationInterface;
}
