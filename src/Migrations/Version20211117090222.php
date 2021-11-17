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

final class Version20211117090222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MADCODERS Sylius RMA Plugin init tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE madcoders_rma_auth_code (id INT AUTO_INCREMENT NOT NULL, order_number LONGTEXT NOT NULL, hash LONGTEXT NOT NULL, auth_code INT NOT NULL, attempts INT NOT NULL, expires_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_configuration (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, parameter LONGTEXT DEFAULT NULL, value LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_881858F272F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return (id INT AUTO_INCREMENT NOT NULL, order_number VARCHAR(255) NOT NULL, return_number VARCHAR(255) NOT NULL, customer_number VARCHAR(255) NOT NULL, channel_code VARCHAR(255) NOT NULL, return_reason VARCHAR(255) DEFAULT NULL, order_consents JSON NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(255) DEFAULT NULL, customer_email VARCHAR(255) DEFAULT NULL, country_code VARCHAR(255) DEFAULT NULL, province_code VARCHAR(255) DEFAULT NULL, province_name VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postcode VARCHAR(255) DEFAULT NULL, order_return_status VARCHAR(255) NOT NULL, customer_ip VARCHAR(255) DEFAULT NULL, customer_note LONGTEXT DEFAULT NULL, bank_account_number VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_9C038725D24D858B (return_number), INDEX IDX_9C038725551F0F81 (order_number), INDEX IDX_9C0387252755C305 (customer_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return_change_log (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, return_number VARCHAR(255) NOT NULL, type LONGTEXT NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8FE15636F675F31B (author_id), INDEX IDX_8FE15636D24D858B (return_number), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return_change_log_author (id INT AUTO_INCREMENT NOT NULL, type LONGTEXT DEFAULT NULL, first_name LONGTEXT DEFAULT NULL, last_name LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return_consent (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, position INT NOT NULL, enabled TINYINT(1) NOT NULL, consent_require TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_791FE99C77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return_consent_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_55DC0AC52C2AC5D3 (translatable_id), UNIQUE INDEX slug_uidx (locale, slug), UNIQUE INDEX madcoders_rma_order_return_consent_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return_item (id INT AUTO_INCREMENT NOT NULL, order_return_id INT NOT NULL, item_to_return TINYINT(1) NOT NULL, product_sku LONGTEXT NOT NULL, product_name LONGTEXT DEFAULT NULL, return_qty INT NOT NULL, max_qty INT NOT NULL, unit_price INT NOT NULL, INDEX IDX_E42300BC9EF36D2D (order_return_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return_reason (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, deadline_to_return INT NOT NULL, position INT NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_EC8D791877153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE madcoders_rma_order_return_reason_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, locale VARCHAR(255) NOT NULL, INDEX IDX_AA56F0AB2C2AC5D3 (translatable_id), UNIQUE INDEX slug_uidx (locale, slug), UNIQUE INDEX madcoders_rma_order_return_reason_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE madcoders_rma_configuration ADD CONSTRAINT FK_881858F272F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_change_log ADD CONSTRAINT FK_8FE15636F675F31B FOREIGN KEY (author_id) REFERENCES madcoders_rma_order_return_change_log_author (id)');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_consent_translation ADD CONSTRAINT FK_55DC0AC52C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES madcoders_rma_order_return_consent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_item ADD CONSTRAINT FK_E42300BC9EF36D2D FOREIGN KEY (order_return_id) REFERENCES madcoders_rma_order_return (id)');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_translation ADD CONSTRAINT FK_AA56F0AB2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES madcoders_rma_order_return_reason (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE madcoders_rma_order_return_item DROP FOREIGN KEY FK_E42300BC9EF36D2D');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_change_log DROP FOREIGN KEY FK_8FE15636F675F31B');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_consent_translation DROP FOREIGN KEY FK_55DC0AC52C2AC5D3');
        $this->addSql('ALTER TABLE madcoders_rma_order_return_reason_translation DROP FOREIGN KEY FK_AA56F0AB2C2AC5D3');
        $this->addSql('DROP TABLE madcoders_rma_auth_code');
        $this->addSql('DROP TABLE madcoders_rma_configuration');
        $this->addSql('DROP TABLE madcoders_rma_order_return');
        $this->addSql('DROP TABLE madcoders_rma_order_return_change_log');
        $this->addSql('DROP TABLE madcoders_rma_order_return_change_log_author');
        $this->addSql('DROP TABLE madcoders_rma_order_return_consent');
        $this->addSql('DROP TABLE madcoders_rma_order_return_consent_translation');
        $this->addSql('DROP TABLE madcoders_rma_order_return_item');
        $this->addSql('DROP TABLE madcoders_rma_order_return_reason');
        $this->addSql('DROP TABLE madcoders_rma_order_return_reason_translation');
    }
}
