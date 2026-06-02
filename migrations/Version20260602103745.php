<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260602103745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_manager CHANGE locale locale VARCHAR(255) DEFAULT \'de\' NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE registration_completed_at registration_completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE construction_site CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE deleted_at deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE construction_site_image CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE craftsman CHANGE last_email_received last_email_received DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_visit_online last_visit_online DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE deleted_at deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE email CHANGE sent_at sent_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE read_at read_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE email_template CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE filter CHANGE registered_at_after registered_at_after DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE registered_at_before registered_at_before DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE resolved_at_after resolved_at_after DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE resolved_at_before resolved_at_before DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE closed_at_after closed_at_after DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE closed_at_before closed_at_before DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE access_allowed_before access_allowed_before DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_used_at last_used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE deadline_before deadline_before DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE deadline_after deadline_after DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at_after created_at_after DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at_before created_at_before DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE issue CHANGE deadline deadline DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE deleted_at deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE registered_at registered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE resolved_at resolved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE closed_at closed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE issue_event CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE deleted_at deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE timestamp timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE issue_event_file CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE issue_image CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE map CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE deleted_at deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE map_file CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_changed_at last_changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE task CHANGE deadline deadline DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE closed_at closed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE craftsman CHANGE last_email_received last_email_received DATETIME DEFAULT NULL, CHANGE last_visit_online last_visit_online DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE construction_manager CHANGE locale locale LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL, CHANGE registration_completed_at registration_completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE email_template CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE map_file CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE construction_site_image CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE email CHANGE sent_at sent_at DATETIME NOT NULL, CHANGE read_at read_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE map CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE filter CHANGE deadline_before deadline_before DATETIME DEFAULT NULL, CHANGE deadline_after deadline_after DATETIME DEFAULT NULL, CHANGE created_at_after created_at_after DATETIME DEFAULT NULL, CHANGE created_at_before created_at_before DATETIME DEFAULT NULL, CHANGE registered_at_after registered_at_after DATETIME DEFAULT NULL, CHANGE registered_at_before registered_at_before DATETIME DEFAULT NULL, CHANGE resolved_at_after resolved_at_after DATETIME DEFAULT NULL, CHANGE resolved_at_before resolved_at_before DATETIME DEFAULT NULL, CHANGE closed_at_after closed_at_after DATETIME DEFAULT NULL, CHANGE closed_at_before closed_at_before DATETIME DEFAULT NULL, CHANGE access_allowed_before access_allowed_before DATETIME DEFAULT NULL, CHANGE last_used_at last_used_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE issue_event CHANGE timestamp timestamp DATETIME NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE issue CHANGE deadline deadline DATETIME DEFAULT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE registered_at registered_at DATETIME DEFAULT NULL, CHANGE resolved_at resolved_at DATETIME DEFAULT NULL, CHANGE closed_at closed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE task CHANGE deadline deadline DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE closed_at closed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE issue_image CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE issue_event_file CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE construction_site CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL, CHANGE deleted_at deleted_at DATETIME DEFAULT NULL');
    }
}
