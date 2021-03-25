<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RmaTimeAgoExtension extends AbstractExtension
{
    /** {@inheritdoc} */
    public function getFunctions()
    {
        return [
            new TwigFunction('rma_time_ago_view', [$this, 'createTimeAgo']),
        ];
    }

    public function createTimeAgo(\DateTime $date): string
    {
        $nowDate = new \DateTime();
        $diff = $nowDate->getTimestamp() - $date->getTimestamp();

        $units = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($units as $unit => $val) {
            if ($diff < $unit) continue;
            $numberOfUnits = floor($diff / $unit);
            return ($val == 'second')? 'a few seconds ago' :
                (($numberOfUnits>1) ? $numberOfUnits : 'a')
                .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
        }
    }
}
