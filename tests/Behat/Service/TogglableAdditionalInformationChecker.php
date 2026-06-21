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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Service;

use Madcoders\SyliusRmaPlugin\Services\AdditionalInformation\AdditionalInformationCheckerInterface;

/**
 * Test double for the additional-information feature flag. The production flag is a compile-time
 * container parameter, so it cannot be flipped per scenario. This implementation stores the state
 * in a marker file instead, which survives the kernel reboots that happen between the Behat step
 * (where the state is set) and the HTTP request that renders/validates the form. A missing file
 * means the flag is off, mirroring the default.
 */
final class TogglableAdditionalInformationChecker implements AdditionalInformationCheckerInterface
{
    public function __construct(private readonly string $markerFile)
    {
    }

    public function isRequired(): bool
    {
        return file_exists($this->markerFile);
    }

    public function enable(): void
    {
        if (!file_exists($this->markerFile)) {
            touch($this->markerFile);
        }
    }

    public function disable(): void
    {
        if (file_exists($this->markerFile)) {
            unlink($this->markerFile);
        }
    }
}
