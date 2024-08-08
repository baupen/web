<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240807115435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE protocol_entry (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', root VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, payload VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, created_by VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_E54577104994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE protocol_entry ADD CONSTRAINT FK_E54577104994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('
            INSERT INTO protocol_entry
                (id, construction_site_id, root, type, payload, created_at, created_by)
            SELECT
                UUID() as id,
                construction_site_id,
                id as root,
                \'STATUS_SET\' as type,
                \'CREATED\' as payload,
                created_at as created_at,
                created_by_id as created_by_id
            FROM issue WHERE created_at IS NOT NULL
        ');
        $this->addSql('
            INSERT INTO protocol_entry
                (id, construction_site_id, root, type, payload, created_at, created_by)
            SELECT
                UUID() as id,
                construction_site_id,
                id as root,
                \'STATUS_SET\' as type,
                \'REGISTERED\' as payload,
                registered_at as created_at,
                registered_by_id as created_by_id
            FROM issue WHERE registered_at IS NOT NULL
        ');
        $this->addSql('
            INSERT INTO protocol_entry
                (id, construction_site_id, root, type, payload, created_at, created_by)
            SELECT
                UUID() as id,
                construction_site_id,
                id as root,
                \'STATUS_SET\' as type,
                \'RESOLVED\' as payload,
                resolved_at as created_at,
                resolved_by_id as created_by_id
            FROM issue WHERE resolved_at IS NOT NULL
        ');
        $this->addSql('
            INSERT INTO protocol_entry
                (id, construction_site_id, root, type, payload, created_at, created_by)
            SELECT
                UUID() as id,
                construction_site_id,
                id as root,
                \'STATUS_SET\' as type,
                \'CLOSED\' as payload,
                closed_at as created_at,
                closed_by_id as created_by_id
            FROM issue WHERE closed_at IS NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE protocol_entry');
    }
}
