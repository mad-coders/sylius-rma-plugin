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
 * Adds the per-product "non-returnable" flag (NonReturnableProductTrait) to the Sylius product table.
 * Existing products default to returnable, so behaviour is unchanged until a product is flagged.
 */
final class Version20260615000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add non_returnable flag to sylius_product';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            !$schema->hasTable('sylius_product'),
            'sylius_product table not found; skipping non_returnable column.',
        );
        $this->skipIf(
            $schema->getTable('sylius_product')->hasColumn('non_returnable'),
            'sylius_product.non_returnable already exists.',
        );

        $this->addSql('ALTER TABLE sylius_product ADD non_returnable TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(
            !$schema->hasTable('sylius_product') || !$schema->getTable('sylius_product')->hasColumn('non_returnable'),
            'sylius_product.non_returnable not present.',
        );

        $this->addSql('ALTER TABLE sylius_product DROP non_returnable');
    }
}
