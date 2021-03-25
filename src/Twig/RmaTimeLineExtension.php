<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
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
            default:
                throw new \Exception('Type not identified');
        }
    }
}
