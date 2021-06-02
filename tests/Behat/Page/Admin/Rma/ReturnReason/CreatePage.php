<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnReason;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Behaviour\ChoosesFormElement;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    use ChoosesFormElement;
}
