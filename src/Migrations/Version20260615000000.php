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
 * Adds the nullable account_holder_name and bank_name columns backing the "Additional information"
 * section of the return form. Both are nullable so existing returns (created before this change)
 * keep loading without data.
 */
final class Version20260615000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add account_holder_name and bank_name columns to madcoders_rma_order_return';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE madcoders_rma_order_return ADD account_holder_name VARCHAR(255) DEFAULT NULL, ADD bank_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE madcoders_rma_order_return DROP account_holder_name, DROP bank_name');
    }
}
