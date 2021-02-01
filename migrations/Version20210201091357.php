<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210201091357 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE construction_manager (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', given_name LONGTEXT DEFAULT NULL, family_name LONGTEXT DEFAULT NULL, phone LONGTEXT DEFAULT NULL, locale LONGTEXT NOT NULL, authorization_authority LONGTEXT DEFAULT NULL, is_admin_account TINYINT(1) DEFAULT \'0\' NOT NULL, can_associate_self TINYINT(1) DEFAULT \'0\' NOT NULL, is_external_account TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, authentication_token LONGTEXT NOT NULL, email VARCHAR(255) NOT NULL, password LONGTEXT DEFAULT NULL, authentication_hash LONGTEXT DEFAULT NULL, is_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_450D96CDE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE construction_site (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name LONGTEXT NOT NULL, folder_name LONGTEXT NOT NULL, is_trial_construction_site TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address LONGTEXT NOT NULL, postal_code INT NOT NULL, locality LONGTEXT NOT NULL, country LONGTEXT NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE construction_site_construction_manager (construction_site_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_manager_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_BC4D21F04994A532 (construction_site_id), INDEX IDX_BC4D21F0A69C9147 (construction_manager_id), PRIMARY KEY(construction_site_id, construction_manager_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE construction_site_image (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename LONGTEXT NOT NULL, hash LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_DFC5717E4994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE craftsman (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', contact_name LONGTEXT NOT NULL, company LONGTEXT NOT NULL, trade LONGTEXT NOT NULL, email LONGTEXT NOT NULL, email_ccs LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', last_email_received DATETIME DEFAULT NULL, last_visit_online DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, authentication_token LONGTEXT NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_7DC593834994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sent_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', identifier CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', link VARCHAR(255) DEFAULT NULL, body VARCHAR(255) DEFAULT NULL, type INT NOT NULL, sent_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, INDEX IDX_E7927C74A45BB98C (sent_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email_template (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, body VARCHAR(255) NOT NULL, purpose INT DEFAULT NULL, self_bcc TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, INDEX IDX_9C0600CA4994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', is_marked TINYINT(1) DEFAULT NULL, state INT DEFAULT NULL, registered_at_after DATETIME DEFAULT NULL, registered_at_before DATETIME DEFAULT NULL, resolved_at_after DATETIME DEFAULT NULL, resolved_at_before DATETIME DEFAULT NULL, closed_at_after DATETIME DEFAULT NULL, closed_at_before DATETIME DEFAULT NULL, deadline_at_before DATETIME DEFAULT NULL, deadline_at_after DATETIME DEFAULT NULL, craftsman_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', craftsman_trades LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', map_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', access_allowed_before DATETIME DEFAULT NULL, authentication_token LONGTEXT NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, INDEX IDX_7FC45F1D4994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE issue (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', craftsman_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', map_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', registered_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', resolved_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', closed_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', number INT NOT NULL, is_marked TINYINT(1) NOT NULL, was_added_with_client TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, deadline DATETIME DEFAULT NULL, last_changed_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, position_zoom_scale DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, registered_at DATETIME DEFAULT NULL, resolved_at DATETIME DEFAULT NULL, closed_at DATETIME DEFAULT NULL, INDEX IDX_12AD233E34508F72 (craftsman_id), INDEX IDX_12AD233E53C55F64 (map_id), INDEX IDX_12AD233E4994A532 (construction_site_id), INDEX IDX_12AD233EB03A8386 (created_by_id), INDEX IDX_12AD233E27E92E18 (registered_by_id), INDEX IDX_12AD233E6713A32B (resolved_by_id), INDEX IDX_12AD233EE1FA7797 (closed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE issue_image (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', issue_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename LONGTEXT NOT NULL, hash LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_57B76D0C5E7AA58C (issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE map (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name LONGTEXT NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_93ADAABB4994A532 (construction_site_id), INDEX IDX_93ADAABB727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE map_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', map_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename LONGTEXT NOT NULL, hash LONGTEXT NOT NULL, INDEX IDX_1FB791854994A532 (construction_site_id), UNIQUE INDEX UNIQ_1FB7918553C55F64 (map_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE construction_site_construction_manager ADD CONSTRAINT FK_BC4D21F04994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE construction_site_construction_manager ADD CONSTRAINT FK_BC4D21F0A69C9147 FOREIGN KEY (construction_manager_id) REFERENCES construction_manager (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE construction_site_image ADD CONSTRAINT FK_DFC5717E4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE craftsman ADD CONSTRAINT FK_7DC593834994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE email ADD CONSTRAINT FK_E7927C74A45BB98C FOREIGN KEY (sent_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE email_template ADD CONSTRAINT FK_9C0600CA4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1D4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E34508F72 FOREIGN KEY (craftsman_id) REFERENCES craftsman (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E53C55F64 FOREIGN KEY (map_id) REFERENCES map (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EB03A8386 FOREIGN KEY (created_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E27E92E18 FOREIGN KEY (registered_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E6713A32B FOREIGN KEY (resolved_by_id) REFERENCES craftsman (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EE1FA7797 FOREIGN KEY (closed_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue_image ADD CONSTRAINT FK_57B76D0C5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE map ADD CONSTRAINT FK_93ADAABB4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE map ADD CONSTRAINT FK_93ADAABB727ACA70 FOREIGN KEY (parent_id) REFERENCES map (id)');
        $this->addSql('ALTER TABLE map_file ADD CONSTRAINT FK_1FB791854994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE map_file ADD CONSTRAINT FK_1FB7918553C55F64 FOREIGN KEY (map_id) REFERENCES map (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site_construction_manager DROP FOREIGN KEY FK_BC4D21F0A69C9147');
        $this->addSql('ALTER TABLE email DROP FOREIGN KEY FK_E7927C74A45BB98C');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EB03A8386');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E27E92E18');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EE1FA7797');
        $this->addSql('ALTER TABLE construction_site_construction_manager DROP FOREIGN KEY FK_BC4D21F04994A532');
        $this->addSql('ALTER TABLE construction_site_image DROP FOREIGN KEY FK_DFC5717E4994A532');
        $this->addSql('ALTER TABLE craftsman DROP FOREIGN KEY FK_7DC593834994A532');
        $this->addSql('ALTER TABLE email_template DROP FOREIGN KEY FK_9C0600CA4994A532');
        $this->addSql('ALTER TABLE filter DROP FOREIGN KEY FK_7FC45F1D4994A532');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E4994A532');
        $this->addSql('ALTER TABLE map DROP FOREIGN KEY FK_93ADAABB4994A532');
        $this->addSql('ALTER TABLE map_file DROP FOREIGN KEY FK_1FB791854994A532');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E34508F72');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E6713A32B');
        $this->addSql('ALTER TABLE issue_image DROP FOREIGN KEY FK_57B76D0C5E7AA58C');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E53C55F64');
        $this->addSql('ALTER TABLE map DROP FOREIGN KEY FK_93ADAABB727ACA70');
        $this->addSql('ALTER TABLE map_file DROP FOREIGN KEY FK_1FB7918553C55F64');
        $this->addSql('DROP TABLE construction_manager');
        $this->addSql('DROP TABLE construction_site');
        $this->addSql('DROP TABLE construction_site_construction_manager');
        $this->addSql('DROP TABLE construction_site_image');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE email_template');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE issue');
        $this->addSql('DROP TABLE issue_image');
        $this->addSql('DROP TABLE map');
        $this->addSql('DROP TABLE map_file');
    }
}
