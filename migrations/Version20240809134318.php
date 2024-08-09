<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240809134318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE protocol_entry_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_for_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename LONGTEXT NOT NULL, hash LONGTEXT NOT NULL, INDEX IDX_12EA012E2F97E6E2 (created_for_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE protocol_entry_file ADD CONSTRAINT FK_12EA012E2F97E6E2 FOREIGN KEY (created_for_id) REFERENCES protocol_entry (id)');
        $this->addSql('ALTER TABLE protocol_entry ADD file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE payload payload LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE protocol_entry ADD CONSTRAINT FK_E545771093CB796C FOREIGN KEY (file_id) REFERENCES protocol_entry_file (id)');
        $this->addSql('CREATE INDEX IDX_E545771093CB796C ON protocol_entry (file_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE protocol_entry DROP FOREIGN KEY FK_E545771093CB796C');
        $this->addSql('DROP TABLE protocol_entry_file');
        $this->addSql('DROP INDEX IDX_E545771093CB796C ON protocol_entry');
        $this->addSql('ALTER TABLE protocol_entry DROP file_id, CHANGE payload payload VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
