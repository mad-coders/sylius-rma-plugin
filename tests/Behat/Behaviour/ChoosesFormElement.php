<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Behaviour;

use Sylius\Behat\Behaviour\DocumentAccessor;

trait ChoosesFormElement
{
    use DocumentAccessor;

    public function choosesFormElement(string $name, string $element): void
    {
        $this->getDocument()->fillField($element, $name);
    }
}
