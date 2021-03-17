<?php
/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
 */
declare(strict_types=1);

namespace Madcoders\SyliusRmaPlugin\Generator;

use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnNumberGenerator
{
    /**
     * @var RepositoryInterface
     */
    private $orderReturnRepository;

    /**
     * ReturnNumberGenerator constructor.
     * @param RepositoryInterface $orderReturnRepository
     */
    public function __construct(RepositoryInterface $orderReturnRepository)
    {
        $this->orderReturnRepository = $orderReturnRepository;
    }

    public function returnNumberGenerate(string $orderNumber): string
    {
        $returnOrderNumberId = 1;
        $returnOrderNumber = 'R' . $orderNumber . '-' . $returnOrderNumberId;

        while ($this->orderReturnRepository->findOneBy(array('returnNumber' => $returnOrderNumber))) {
            $returnOrderNumberId++;
            $returnOrderNumber = 'R' . $orderNumber . '-' . $returnOrderNumberId;
        }

        return $returnOrderNumber;
    }
}
