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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnConsent;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Behaviour\ChoosesFormElement;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Behaviour\SelectFormElement;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    use ChoosesFormElement;
    use SelectFormElement;

    public function selectFieldType(string $value): void
    {
        $this->getDocument()->selectFieldOption('madcoders_rma_return_consent_fieldType', $value);
    }

    public function isSlugFieldMarkedRequired(string $localeCode): bool
    {
        $slug = $this->getDocument()->findField(sprintf('madcoders_rma_return_consent_translations_%s_slug', $localeCode));

        return null !== $slug && $slug->hasAttribute('required');
    }
}
