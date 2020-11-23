<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201123075539 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authentication_token ADD filter_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD craftsman_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD last_used_at DATETIME DEFAULT NULL, ADD access_allowed_before DATETIME DEFAULT NULL, DROP last_used');
        $this->addSql('ALTER TABLE authentication_token ADD CONSTRAINT FK_B54C4ADDD395B25E FOREIGN KEY (filter_id) REFERENCES filter (id)');
        $this->addSql('ALTER TABLE authentication_token ADD CONSTRAINT FK_B54C4ADD34508F72 FOREIGN KEY (craftsman_id) REFERENCES craftsman (id)');
        $this->addSql('CREATE INDEX IDX_B54C4ADDD395B25E ON authentication_token (filter_id)');
        $this->addSql('CREATE INDEX IDX_B54C4ADD34508F72 ON authentication_token (craftsman_id)');
        $this->addSql('ALTER TABLE craftsman DROP email_identifier, DROP write_authorization_token');
        $this->addSql('ALTER TABLE filter DROP access_allowed_until, DROP last_access');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authentication_token DROP FOREIGN KEY FK_B54C4ADDD395B25E');
        $this->addSql('ALTER TABLE authentication_token DROP FOREIGN KEY FK_B54C4ADD34508F72');
        $this->addSql('DROP INDEX IDX_B54C4ADDD395B25E ON authentication_token');
        $this->addSql('DROP INDEX IDX_B54C4ADD34508F72 ON authentication_token');
        $this->addSql('ALTER TABLE authentication_token ADD last_used DATETIME NOT NULL, DROP filter_id, DROP craftsman_id, DROP last_used_at, DROP access_allowed_before');
        $this->addSql('ALTER TABLE craftsman ADD email_identifier LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD write_authorization_token LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE filter ADD access_allowed_until DATETIME DEFAULT NULL, ADD last_access DATETIME DEFAULT NULL');
    }
}
