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
    /** @inheritdoc */
    public function getFunctions()
    {
        return [
            new TwigFunction('rma_time_line_item_view', $this->createTimeLineItemView(...)),
        ];
    }

    public function createTimeLineItemView(OrderReturnChangeLog $changeLog): string
    {
        $changeLogType = $changeLog->getType();

        return match ($changeLogType) {
            'added_note' => '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_addedNote.html.twig',
            'created_draft' => '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_createdDraft.html.twig',
            'customer_accepted' => '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_customerAccepted.html.twig',
            'cancelled' => '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_cancelled.html.twig',
            'completed' => '@MadcodersSyliusRmaPlugin/Admin/Return/Show/Management/Timeline/_completed.html.twig',
            default => throw new \Exception('Type not identified'),
        };
    }
}
