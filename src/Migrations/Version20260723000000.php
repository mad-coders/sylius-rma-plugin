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
 * Adds the field_type column backing the consent field type ("external_page" or "inline"). It is
 * NOT NULL with a default of "external_page" so consents created before this change keep their
 * current page-based behaviour with no data edit.
 */
final class Version20260723000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add field_type column to madcoders_rma_order_return_consent';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE madcoders_rma_order_return_consent ADD field_type VARCHAR(255) DEFAULT 'external_page' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE madcoders_rma_order_return_consent DROP field_type');
    }
}
