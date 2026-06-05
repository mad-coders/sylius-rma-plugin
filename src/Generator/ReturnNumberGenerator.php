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

namespace Madcoders\SyliusRmaPlugin\Generator;

use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnNumberGenerator
{
    /**
     * ReturnNumberGenerator constructor.
     */
    public function __construct(private readonly RepositoryInterface $orderReturnRepository)
    {
    }

    public function returnNumberGenerate(string $orderNumber): string
    {
        $returnOrderNumberId = 1;
        $returnOrderNumber = 'R' . $orderNumber . '-' . $returnOrderNumberId;

        while ($this->orderReturnRepository->findOneBy(['returnNumber' => $returnOrderNumber])) {
            ++$returnOrderNumberId;
            $returnOrderNumber = 'R' . $orderNumber . '-' . $returnOrderNumberId;
        }

        return $returnOrderNumber;
    }
}
