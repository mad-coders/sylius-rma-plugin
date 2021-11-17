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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Page\Admin\Rma\ReturnReason;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;
use Tests\Madcoders\SyliusRmaPlugin\Behat\Behaviour\ChoosesFormElement;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    use ChoosesFormElement;
}
