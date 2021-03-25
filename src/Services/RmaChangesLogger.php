<?php

/*
 * This file is part of the Madcoders RMA Plugin.
 *
 * (c) Leonid Moshko
 *
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
