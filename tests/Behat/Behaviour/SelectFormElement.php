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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Behaviour;

use Sylius\Behat\Behaviour\DocumentAccessor;

trait SelectFormElement
{
    use DocumentAccessor;

    public function selectsOption(string $locator, string $value): void
    {
        $this->getDocument()->selectFieldOption($locator, $value);
    }
}
