<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241110141419 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE issue_event ADD last_changed_by VARCHAR(255), ADD timestamp DATETIME, ADD last_changed_at DATETIME');
        $this->addSql('UPDATE issue_event SET last_changed_by = created_by, timestamp = created_at, last_changed_at = created_at;');
        $this->addSql('ALTER TABLE issue_event MODIFY last_changed_by VARCHAR(255) NOT NULL, MODIFY timestamp DATETIME NOT NULL, MODIFY last_changed_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE issue_event DROP last_changed_by, DROP timestamp, DROP last_changed_at');
    }
}
