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

namespace Madcoders\SyliusRmaPlugin\Services;

use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLog;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnChangeLogAuthor;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class RmaChangesLogger
{
    /** @var RepositoryInterface */
    private $changeLogRepository;

    /**
     * RmaChangesLogger constructor.
     * @param RepositoryInterface $changeLogRepository
     */
    public function __construct(RepositoryInterface $changeLogRepository)
    {
        $this->changeLogRepository = $changeLogRepository;
    }

    public function add(
        string $returnNumber,
        string $type,
        string $note,
        OrderReturnChangeLogAuthor $newChangeLogAuthor
    )
    {
        $newChangeLog = new OrderReturnChangeLog();
        $newChangeLog->setReturnNumber($returnNumber);
        $newChangeLog->setType($type);
        $newChangeLog->setNote($note);
        $newChangeLog->setAuthor($newChangeLogAuthor);

        $this->changeLogRepository->add($newChangeLog);
    }
}
