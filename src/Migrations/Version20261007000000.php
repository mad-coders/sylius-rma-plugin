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
 * Adds per-order grace periods for return reasons and their audit log (#67). An admin can grant
 * extra days on top of a reason's deadline for one order; one row per order and reason. Both
 * tables reference sylius_order and are removed together with the order. Orders without a grace
 * period behave exactly as before, so no data change is needed.
 */
final class Version20261007000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create madcoders_rma_order_return_reason_grace_period and its audit log table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE madcoders_rma_order_return_reason_grace_period (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, reason_id INT NOT NULL, extra_days INT NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7F61CB0559BB1592 (reason_id), UNIQUE INDEX madcoders_rma_grace_period_order_reason_uidx (order_id, reason_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return_reason_grace_period_log (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, author_id INT NOT NULL, reason_code VARCHAR(255) NOT NULL, action VARCHAR(255) NOT NULL, previous_extra_days INT DEFAULT NULL, extra_days INT DEFAULT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_D42CCEE78D9F6D38 (order_id), UNIQUE INDEX UNIQ_D42CCEE7F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_grace_period ADD CONSTRAINT FK_7F61CB058D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_grace_period ADD CONSTRAINT FK_7F61CB0559BB1592 FOREIGN KEY (reason_id) REFERENCES madcoders_rma_order_return_reason (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_grace_period_log ADD CONSTRAINT FK_D42CCEE78D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_grace_period_log ADD CONSTRAINT FK_D42CCEE7F675F31B FOREIGN KEY (author_id) REFERENCES madcoders_rma_order_return_change_log_author (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_grace_period DROP FOREIGN KEY FK_7F61CB058D9F6D38');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_grace_period DROP FOREIGN KEY FK_7F61CB0559BB1592');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_grace_period_log DROP FOREIGN KEY FK_D42CCEE78D9F6D38');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_grace_period_log DROP FOREIGN KEY FK_D42CCEE7F675F31B');
        $this->addSql('DROP TABLE madcoders_rma_order_return_reason_grace_period');
        $this->addSql('DROP TABLE madcoders_rma_order_return_reason_grace_period_log');
    }
}
