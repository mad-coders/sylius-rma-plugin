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
 * Renames the order-return status "cancellation_request" to "withdrawal_request" after the
 * withdrawal state-machine unification. Historical "canceled" rows are intentionally left untouched:
 * they are indistinguishable from admin-cancelled returns, so they keep the "canceled" status.
 */
final class Version20260612000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename order return status cancellation_request to withdrawal_request';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE madcoders_rma_order_return SET order_return_status = 'withdrawal_request' WHERE order_return_status = 'cancellation_request'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE madcoders_rma_order_return SET order_return_status = 'cancellation_request' WHERE order_return_status = 'withdrawal_request'");
    }
}
