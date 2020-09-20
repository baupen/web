<?php

declare(strict_types=1);

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200920083046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authentication_token (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_manager_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', token LONGTEXT NOT NULL, last_used DATETIME NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, INDEX IDX_B54C4ADDA69C9147 (construction_manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE construction_manager (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', active_construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', given_name LONGTEXT DEFAULT NULL, family_name LONGTEXT DEFAULT NULL, phone LONGTEXT DEFAULT NULL, locale LONGTEXT NOT NULL, is_admin_account TINYINT(1) DEFAULT \'0\' NOT NULL, is_trial_account TINYINT(1) DEFAULT \'0\' NOT NULL, is_external_account TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, email TINYTEXT NOT NULL, password LONGTEXT DEFAULT NULL, authentication_hash LONGTEXT DEFAULT NULL, is_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_450D96CDE7927C74 (email), INDEX IDX_450D96CDA7F12217 (active_construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE construction_site (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name LONGTEXT NOT NULL, folder_name LONGTEXT NOT NULL, is_trial_construction_site TINYINT(1) DEFAULT \'0\' NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address LONGTEXT DEFAULT NULL, postal_code INT DEFAULT NULL, locality LONGTEXT DEFAULT NULL, country LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_BF4E61B43DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE construction_site_construction_manager (construction_site_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_manager_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_BC4D21F04994A532 (construction_site_id), INDEX IDX_BC4D21F0A69C9147 (construction_manager_id), PRIMARY KEY(construction_site_id, construction_manager_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE construction_site_image (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename LONGTEXT NOT NULL, hash LONGTEXT NOT NULL, INDEX IDX_DFC5717E4994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE craftsman (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', contact_name LONGTEXT NOT NULL, company LONGTEXT NOT NULL, trade LONGTEXT NOT NULL, email LONGTEXT NOT NULL, last_email_sent DATETIME DEFAULT NULL, last_online_visit DATETIME DEFAULT NULL, email_identifier LONGTEXT NOT NULL, write_authorization_token LONGTEXT NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address LONGTEXT DEFAULT NULL, postal_code INT DEFAULT NULL, locality LONGTEXT DEFAULT NULL, country LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_7DC593834994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', sender_name LONGTEXT DEFAULT NULL, sender_email LONGTEXT DEFAULT NULL, receiver LONGTEXT NOT NULL, subject LONGTEXT NOT NULL, body LONGTEXT NOT NULL, action_text LONGTEXT DEFAULT NULL, action_link LONGTEXT DEFAULT NULL, email_type INT NOT NULL, sent_date_time DATETIME DEFAULT NULL, visited_date_time DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', public_access_identifier LONGTEXT DEFAULT NULL, access_allowed_until DATETIME DEFAULT NULL, last_access DATETIME DEFAULT NULL, is_marked TINYINT(1) DEFAULT NULL, was_added_with_client TINYINT(1) DEFAULT NULL, issues LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', any_status INT DEFAULT NULL, filter_by_issues TINYINT(1) DEFAULT \'0\' NOT NULL, craftsmen LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', filter_by_craftsmen TINYINT(1) DEFAULT \'0\' NOT NULL, trades LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', filter_by_trades TINYINT(1) DEFAULT \'0\' NOT NULL, maps LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', filter_by_maps TINYINT(1) DEFAULT \'0\' NOT NULL, registration_status TINYINT(1) DEFAULT NULL, registration_start DATETIME DEFAULT NULL, registration_end DATETIME DEFAULT NULL, responded_status TINYINT(1) DEFAULT NULL, responded_start DATETIME DEFAULT NULL, responded_end DATETIME DEFAULT NULL, reviewed_status TINYINT(1) DEFAULT NULL, reviewed_start DATETIME DEFAULT NULL, reviewed_end DATETIME DEFAULT NULL, limit_start DATETIME DEFAULT NULL, limit_end DATETIME DEFAULT NULL, INDEX IDX_7FC45F1D4994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE issue (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', upload_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', registration_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', response_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', review_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', craftsman_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', map_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', map_file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', number INT DEFAULT NULL, is_marked TINYINT(1) NOT NULL, was_added_with_client TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, response_limit DATETIME DEFAULT NULL, position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, position_zoom_scale DOUBLE PRECISION DEFAULT NULL, uploaded_at DATETIME NOT NULL, registered_at DATETIME DEFAULT NULL, responded_at DATETIME DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_12AD233E83BA6D1B (upload_by_id), INDEX IDX_12AD233E254D80EC (registration_by_id), INDEX IDX_12AD233EBA91FB54 (response_by_id), INDEX IDX_12AD233EB9690C1F (review_by_id), INDEX IDX_12AD233E3DA5256D (image_id), INDEX IDX_12AD233E34508F72 (craftsman_id), INDEX IDX_12AD233E53C55F64 (map_id), INDEX IDX_12AD233EBCE08130 (map_file_id), INDEX IDX_12AD233E4994A532 (construction_site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE issue_image (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', issue_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename LONGTEXT NOT NULL, hash LONGTEXT NOT NULL, INDEX IDX_57B76D0C5E7AA58C (issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE map (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name LONGTEXT NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_93ADAABB4994A532 (construction_site_id), INDEX IDX_93ADAABB727ACA70 (parent_id), INDEX IDX_93ADAABB93CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE map_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', map_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename LONGTEXT NOT NULL, hash LONGTEXT NOT NULL, INDEX IDX_1FB791854994A532 (construction_site_id), INDEX IDX_1FB7918553C55F64 (map_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE authentication_token ADD CONSTRAINT FK_B54C4ADDA69C9147 FOREIGN KEY (construction_manager_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE construction_manager ADD CONSTRAINT FK_450D96CDA7F12217 FOREIGN KEY (active_construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE construction_site ADD CONSTRAINT FK_BF4E61B43DA5256D FOREIGN KEY (image_id) REFERENCES construction_site_image (id)');
        $this->addSql('ALTER TABLE construction_site_construction_manager ADD CONSTRAINT FK_BC4D21F04994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE construction_site_construction_manager ADD CONSTRAINT FK_BC4D21F0A69C9147 FOREIGN KEY (construction_manager_id) REFERENCES construction_manager (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE construction_site_image ADD CONSTRAINT FK_DFC5717E4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE craftsman ADD CONSTRAINT FK_7DC593834994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1D4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E83BA6D1B FOREIGN KEY (upload_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E254D80EC FOREIGN KEY (registration_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EBA91FB54 FOREIGN KEY (response_by_id) REFERENCES craftsman (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EB9690C1F FOREIGN KEY (review_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E3DA5256D FOREIGN KEY (image_id) REFERENCES issue_image (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E34508F72 FOREIGN KEY (craftsman_id) REFERENCES craftsman (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E53C55F64 FOREIGN KEY (map_id) REFERENCES map (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EBCE08130 FOREIGN KEY (map_file_id) REFERENCES map_file (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE issue_image ADD CONSTRAINT FK_57B76D0C5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE map ADD CONSTRAINT FK_93ADAABB4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE map ADD CONSTRAINT FK_93ADAABB727ACA70 FOREIGN KEY (parent_id) REFERENCES map (id)');
        $this->addSql('ALTER TABLE map ADD CONSTRAINT FK_93ADAABB93CB796C FOREIGN KEY (file_id) REFERENCES map_file (id)');
        $this->addSql('ALTER TABLE map_file ADD CONSTRAINT FK_1FB791854994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE map_file ADD CONSTRAINT FK_1FB7918553C55F64 FOREIGN KEY (map_id) REFERENCES map (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authentication_token DROP FOREIGN KEY FK_B54C4ADDA69C9147');
        $this->addSql('ALTER TABLE construction_site_construction_manager DROP FOREIGN KEY FK_BC4D21F0A69C9147');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E83BA6D1B');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E254D80EC');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EB9690C1F');
        $this->addSql('ALTER TABLE construction_manager DROP FOREIGN KEY FK_450D96CDA7F12217');
        $this->addSql('ALTER TABLE construction_site_construction_manager DROP FOREIGN KEY FK_BC4D21F04994A532');
        $this->addSql('ALTER TABLE construction_site_image DROP FOREIGN KEY FK_DFC5717E4994A532');
        $this->addSql('ALTER TABLE craftsman DROP FOREIGN KEY FK_7DC593834994A532');
        $this->addSql('ALTER TABLE filter DROP FOREIGN KEY FK_7FC45F1D4994A532');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E4994A532');
        $this->addSql('ALTER TABLE map DROP FOREIGN KEY FK_93ADAABB4994A532');
        $this->addSql('ALTER TABLE map_file DROP FOREIGN KEY FK_1FB791854994A532');
        $this->addSql('ALTER TABLE construction_site DROP FOREIGN KEY FK_BF4E61B43DA5256D');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EBA91FB54');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E34508F72');
        $this->addSql('ALTER TABLE issue_image DROP FOREIGN KEY FK_57B76D0C5E7AA58C');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E3DA5256D');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E53C55F64');
        $this->addSql('ALTER TABLE map DROP FOREIGN KEY FK_93ADAABB727ACA70');
        $this->addSql('ALTER TABLE map_file DROP FOREIGN KEY FK_1FB7918553C55F64');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EBCE08130');
        $this->addSql('ALTER TABLE map DROP FOREIGN KEY FK_93ADAABB93CB796C');
        $this->addSql('DROP TABLE authentication_token');
        $this->addSql('DROP TABLE construction_manager');
        $this->addSql('DROP TABLE construction_site');
        $this->addSql('DROP TABLE construction_site_construction_manager');
        $this->addSql('DROP TABLE construction_site_image');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE issue');
        $this->addSql('DROP TABLE issue_image');
        $this->addSql('DROP TABLE map');
        $this->addSql('DROP TABLE map_file');
    }
}
