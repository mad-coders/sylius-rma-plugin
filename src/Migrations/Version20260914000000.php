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

namespace Madcoders\SyliusRmaPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Makes the consent translation slug nullable and turns existing empty slugs into NULL. An inline
 * consent has no slug; stored as '' the second one in a locale violated the (locale, slug) unique
 * index, while a unique index accepts any number of NULLs (#69).
 */
final class Version20260914000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make madcoders_rma_order_return_consent_translation.slug nullable and store empty slugs as NULL';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE madcoders_rma_order_return_consent_translation CHANGE slug slug VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE madcoders_rma_order_return_consent_translation SET slug = NULL WHERE TRIM(slug) = ''");
    }

    public function down(Schema $schema): void
    {
        $localesWithSeveralMissingSlugs = $this->connection->fetchFirstColumn(
            'SELECT locale FROM madcoders_rma_order_return_consent_translation WHERE slug IS NULL GROUP BY locale HAVING COUNT(*) > 1',
        );
        $this->abortIf(
            [] !== $localesWithSeveralMissingSlugs,
            'Cannot make the consent slug NOT NULL again: several consent translations in one locale have no slug and would collide on the slug_uidx unique index. Give those consents a slug first.',
        );

        $this->addSql("UPDATE madcoders_rma_order_return_consent_translation SET slug = '' WHERE slug IS NULL");
        $this->addSql('ALTER TABLE madcoders_rma_order_return_consent_translation CHANGE slug slug VARCHAR(255) NOT NULL');
    }
}
