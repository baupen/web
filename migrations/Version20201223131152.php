<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201223131152 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_template (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, body VARCHAR(255) NOT NULL, type INT NOT NULL, self_bcc TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, INDEX IDX_9C0600CA4994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE email_template ADD CONSTRAINT FK_9C0600CA4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE craftsman ADD email_ccs LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE email ADD body VARCHAR(255) DEFAULT NULL, CHANGE sent_date_time sent_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE filter ADD created_at DATETIME, ADD last_changed_at DATETIME');
        $this->addSql('UPDATE filter SET created_at = NOW(), last_changed_at = NOW()');
        $this->addSql('ALTER TABLE filter MODIFY created_at DATETIME NOT NULL, MODIFY last_changed_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE email_template');
        $this->addSql('ALTER TABLE craftsman DROP email_ccs');
        $this->addSql('ALTER TABLE email DROP body, CHANGE sent_at sent_date_time DATETIME NOT NULL');
        $this->addSql('ALTER TABLE filter DROP created_at, DROP last_changed_at');
    }
}
