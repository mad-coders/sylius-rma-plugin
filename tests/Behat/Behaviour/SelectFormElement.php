<?php

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
