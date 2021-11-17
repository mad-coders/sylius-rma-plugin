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

namespace Madcoders\SyliusRmaPlugin\Twig;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLog;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RmaTimeLineExtension extends AbstractExtension
{
    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new TwigFunction('rma_time_line_item_view', [$this, 'createTimeLineItemView']),
        ];
    }

    public function createTimeLineItemView(OrderReturnChangeLog $changeLog): string
    {
        $changeLogType = $changeLog->getType();
        switch ($changeLogType) {
            case 'added_note':
                return '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_addedNote.html.twig';
            case 'created_draft':
                return '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_createdDraft.html.twig';
            case 'customer_accepted':
                return '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_customerAccepted.html.twig';
            case 'cancelled':
                return '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_cancelled.html.twig';
            case 'completed':
                return '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_completed.html.twig';
            default:
                throw new \Exception('Type not identified');
        }
    }
}
