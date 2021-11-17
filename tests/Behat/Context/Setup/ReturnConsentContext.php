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
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsent;
use Madcoders\SyliusRmaPlugin\Entity\OrderReturnConsentInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ReturnConsentContext  implements Context
{
    /** @var RepositoryInterface */
    private $returnConsentRepository;

    /** @var SharedStorageInterface */
    private $sharedStorage;

    /**
     * ReturnConsentContext constructor.
     *
     * @param RepositoryInterface $returnConsentRepository
     * @param SharedStorageInterface $sharedStorage
     */
    public function __construct(
        RepositoryInterface $returnConsentRepository,
        SharedStorageInterface $sharedStorage
    )
    {
        $this->returnConsentRepository = $returnConsentRepository;
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @Given there are return consents:
     */
    public function thereAreConsents(TableNode $table): void
    {
        foreach($table as $row) {
            $this->createOrderReturnConsent($row['code'], $row['name']);
        }
    }

    private function createOrderReturnConsent(string $code, string $name): OrderReturnConsentInterface
    {
        $localeCode = $this->getAdminLocaleCode();
        $consent = new OrderReturnConsent();
        $consent->setCurrentLocale($localeCode);
        $consent->setSlug(StringInflector::nameToSlug($name));
        $consent->setCode($code);
        $consent->setName($name);

        $this->returnConsentRepository->add($consent);

        return $consent;
    }

    private function getAdminLocaleCode(): string
    {
        /** @var AdminUserInterface $adminUser */
        $adminUser = $this->sharedStorage->get('administrator');
        return $adminUser->getLocaleCode();
    }
}
