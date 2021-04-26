<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
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

interface OrderReturnConsentInterface extends CodeAwareInterface, TranslatableInterface, ResourceInterface, TimestampableInterface, SlugAwareInterface, ToggleableInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getPosition(): ?int;

    public function setPosition(?int $position): void;

    public function isConsentRequire(): bool;

    public function setConsentRequire(bool $consentRequire): void;

    /**
     * @param string|null $locale
     * @return OrderReturnConsentTranslationInterface
     */
    public function getTranslation(?string $locale = null): TranslationInterface;
}
