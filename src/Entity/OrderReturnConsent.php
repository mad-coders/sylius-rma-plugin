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

use Doctrine\Common\Comparable;
use Sylius\Component\Resource\Model\TimestampableTrait;
use Sylius\Component\Resource\Model\ToggleableTrait;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Resource\Model\TranslationInterface;

class OrderReturnConsent implements Comparable, OrderReturnConsentInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    use ToggleableTrait;

    use TimestampableTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $code;

    /** @var int|null */
    private $position;

    /** @var bool */
    private $consentRequire = false;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->getTranslation()->getName();
    }

    public function setName(?string $name): void
    {
        $this->getTranslation()->setName($name);
    }

    public static function getTranslationClass(): string
    {
        return OrderReturnConsentTranslation::class;
    }

    public function compareTo($other): int
    {
        return $this->code === $other->getCode() ? 0 : 1;
    }

    public function getDescription(): ?string
    {
        return $this->getTranslation()->getDescription();
    }

    public function setDescription(?string $description): void
    {
        $this->getTranslation()->setDescription($description);
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getSlug(): ?string
    {
        return $this->getTranslation()->getSlug();
    }

    public function setSlug(?string $slug): void
    {
        $this->getTranslation()->setSlug($slug);
    }

    /** @return bool */
    public function isConsentRequire(): bool
    {
        return $this->consentRequire;
    }

    /** @param bool $consentRequire */
    public function setConsentRequire(bool $consentRequire): void
    {
        $this->consentRequire = $consentRequire;
    }

    /**
     * @param string|null $locale
     * @return OrderReturnConsentTranslationInterface
     */
    public function getTranslation(?string $locale = null): TranslationInterface
    {
        /** @var OrderReturnConsentTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    protected function createTranslation(): OrderReturnConsentTranslationInterface
    {
        return new OrderReturnConsentTranslation();
    }
}
