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

namespace Tests\Madcoders\SyliusRmaPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReason;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonInterface;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnReasonTranslation;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Webmozart\Assert\Assert;

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
        foreach ($table as $row) {
            $this->createOrderReturnReason($row['code'], $row['name'], $row['deadline_to_return']);
        }
    }

    /**
     * Stores the translation directly, the way the admin form used to save it before a slug was
     * required on every persisted translation.
     *
     * @Given the return reason :code has a :localeCode translation named :name without a slug
     */
    public function theReturnReasonHasATranslationWithoutASlug(string $code, string $localeCode, string $name): void
    {
        $reason = $this->returnReasonRepository->findOneBy(['code' => $code]);
        Assert::isInstanceOf($reason, OrderReturnReasonInterface::class);

        $translation = new OrderReturnReasonTranslation();
        $translation->setLocale($localeCode);
        $translation->setName($name);
        $translation->setSlug('');
        $reason->addTranslation($translation);

        $this->returnReasonRepository->add($reason);
    }

    private function createOrderReturnReason(string $code, string $name, string $days): OrderReturnReasonInterface
    {
        $reason = new OrderReturnReason();
        $reason->setCurrentLocale('en_US');
        $reason->setSlug(StringInflector::nameToSlug($name));
        $reason->setCode($code);
        $reason->setName($name);
        $reason->setDeadlineToReturn((int) $days);

        $this->returnReasonRepository->add($reason);

        return $reason;
    }
}
