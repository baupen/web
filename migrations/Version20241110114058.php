<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241110114058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE protocol_entry RENAME TO issue_event');
        $this->addSql('ALTER TABLE protocol_entry_file RENAME TO issue_event_file;');
        $this->addSql('ALTER TABLE issue_event DROP FOREIGN KEY FK_E54577104994A532');
        $this->addSql('ALTER TABLE issue_event DROP FOREIGN KEY FK_E545771093CB796C');
        $this->addSql('DROP INDEX idx_e54577104994a532 ON issue_event');
        $this->addSql('CREATE INDEX IDX_A92463F44994A532 ON issue_event (construction_site_id)');
        $this->addSql('DROP INDEX idx_e545771093cb796c ON issue_event');
        $this->addSql('CREATE INDEX IDX_A92463F493CB796C ON issue_event (file_id)');
        $this->addSql('ALTER TABLE issue_event ADD CONSTRAINT FK_E54577104994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE issue_event ADD CONSTRAINT FK_E545771093CB796C FOREIGN KEY (file_id) REFERENCES issue_event_file (id)');
        $this->addSql('ALTER TABLE issue_event_file DROP FOREIGN KEY FK_12EA012E2F97E6E2');
        $this->addSql('DROP INDEX idx_12ea012e2f97e6e2 ON issue_event_file');
        $this->addSql('CREATE INDEX IDX_51C01E72F97E6E2 ON issue_event_file (created_for_id)');
        $this->addSql('ALTER TABLE issue_event_file ADD CONSTRAINT FK_12EA012E2F97E6E2 FOREIGN KEY (created_for_id) REFERENCES issue_event (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue_event DROP FOREIGN KEY FK_A92463F44994A532');
        $this->addSql('ALTER TABLE issue_event DROP FOREIGN KEY FK_A92463F493CB796C');
        $this->addSql('DROP INDEX idx_a92463f44994a532 ON issue_event');
        $this->addSql('CREATE INDEX IDX_E54577104994A532 ON issue_event (construction_site_id)');
        $this->addSql('DROP INDEX idx_a92463f493cb796c ON issue_event');
        $this->addSql('CREATE INDEX IDX_E545771093CB796C ON issue_event (file_id)');
        $this->addSql('ALTER TABLE issue_event ADD CONSTRAINT FK_A92463F44994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE issue_event ADD CONSTRAINT FK_A92463F493CB796C FOREIGN KEY (file_id) REFERENCES issue_event_file (id)');
        $this->addSql('ALTER TABLE issue_event_file DROP FOREIGN KEY FK_51C01E72F97E6E2');
        $this->addSql('DROP INDEX idx_51c01e72f97e6e2 ON issue_event_file');
        $this->addSql('CREATE INDEX IDX_12EA012E2F97E6E2 ON issue_event_file (created_for_id)');
        $this->addSql('ALTER TABLE issue_event_file ADD CONSTRAINT FK_51C01E72F97E6E2 FOREIGN KEY (created_for_id) REFERENCES issue_event (id)');
        $this->addSql('ALTER TABLE issue_event RENAME TO protocol_entry');
        $this->addSql('ALTER TABLE issue_event_file RENAME TO protocol_entry_file;');
    }
}
