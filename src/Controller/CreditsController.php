<?php

declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sylius RMA Plugin
 *
 * @copyright MADCODERS (www.madcoders.co)
 * @licence For the full copyright and license information, please view the LICENSE file
 *
 * Architects of this package:
 * @author Leonid Moshko <l.moshko@madcoders.pl>
 * @author Piotr Lewandowski <p.lewandowski@madcoders.pl>
 */
final class CreditsController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('@MadcodersSyliusRmaPlugin/credits.html.twig');
    }

}
