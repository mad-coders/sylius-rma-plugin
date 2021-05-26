<?php

declare(strict_types=1);

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReason;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnReasonContext implements Context
{
    /** @var RepositoryInterface */
    private $returnReasonRepository;

    public function __construct(RepositoryInterface $returnReasonRepository)
    {
        $this->returnReasonRepository = $returnReasonRepository;
    }

    /**
     * @Given there are return reasons:
     */
    public function thereAreReasons(TableNode $table): void
    {
        foreach($table as $row) {
            $this->createOrderReturnReason($row['code'], $row['name'], $row['deadline_to_return']);
        }
    }

    private function createOrderReturnReason(string $code, string $name, string $days): OrderReturnReasonInterface
    {
        $reason = new OrderReturnReason();
        $reason->setCurrentLocale('en_US');
        $reason->setSlug(StringInflector::nameToSlug($name));
        $reason->setCode($code);
        $reason->setName($name);
        $reason->setDeadlineToReturn((int)$days);

        $this->returnReasonRepository->add($reason);

        return $reason;
    }

}
