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
final class Version20181203164942 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE map (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , parent_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , file_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name CLOB NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, prevent_automatic_edit CLOB DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_93ADAABB4994A532 ON map (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_93ADAABB727ACA70 ON map (parent_id)');
        $this->addSql('CREATE INDEX IDX_93ADAABB93CB796C ON map (file_id)');
        $this->addSql('CREATE TABLE map_file (id CHAR(36) NOT NULL --(DC2Type:guid)
        , map_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename CLOB NOT NULL, display_filename CLOB NOT NULL, hash CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1FB7918553C55F64 ON map_file (map_id)');
        $this->addSql('CREATE TABLE issue (id CHAR(36) NOT NULL --(DC2Type:guid)
        , upload_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , registration_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , response_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , review_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , image_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , craftsman_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , map_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , map_file_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , number INTEGER DEFAULT NULL, is_marked BOOLEAN NOT NULL, was_added_with_client BOOLEAN NOT NULL, description CLOB DEFAULT NULL, response_limit DATETIME DEFAULT NULL, uploaded_at DATETIME NOT NULL, registered_at DATETIME DEFAULT NULL, responded_at DATETIME DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_12AD233E83BA6D1B ON issue (upload_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E254D80EC ON issue (registration_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EBA91FB54 ON issue (response_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EB9690C1F ON issue (review_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E3DA5256D ON issue (image_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E34508F72 ON issue (craftsman_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E53C55F64 ON issue (map_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EBCE08130 ON issue (map_file_id)');
        $this->addSql('CREATE TABLE issue_image (id CHAR(36) NOT NULL --(DC2Type:guid)
        , issue_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename CLOB NOT NULL, display_filename CLOB NOT NULL, hash CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_57B76D0C5E7AA58C ON issue_image (issue_id)');
        $this->addSql('CREATE TABLE construction_site (id CHAR(36) NOT NULL --(DC2Type:guid)
        , image_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name CLOB NOT NULL, folder_name CLOB NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, locality CLOB DEFAULT NULL, country CLOB DEFAULT NULL, prevent_automatic_edit CLOB DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF4E61B43DA5256D ON construction_site (image_id)');
        $this->addSql('CREATE TABLE construction_site_construction_manager (construction_site_id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_manager_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(construction_site_id, construction_manager_id))');
        $this->addSql('CREATE INDEX IDX_BC4D21F04994A532 ON construction_site_construction_manager (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_BC4D21F0A69C9147 ON construction_site_construction_manager (construction_manager_id)');
        $this->addSql('CREATE TABLE construction_site_image (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, filename CLOB NOT NULL, display_filename CLOB NOT NULL, hash CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DFC5717E4994A532 ON construction_site_image (construction_site_id)');
        $this->addSql('CREATE TABLE craftsman (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , share_view_filter_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , contact_name CLOB NOT NULL, company CLOB NOT NULL, trade CLOB NOT NULL, email CLOB NOT NULL, last_email_sent DATETIME DEFAULT NULL, last_online_visit DATETIME DEFAULT NULL, email_identifier CLOB NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, locality CLOB DEFAULT NULL, country CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7DC593834994A532 ON craftsman (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_7DC59383AD2B52E5 ON craftsman (share_view_filter_id)');
        $this->addSql('CREATE TABLE construction_manager (id CHAR(36) NOT NULL --(DC2Type:guid)
        , active_construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , given_name CLOB DEFAULT NULL, family_name CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, locale CLOB DEFAULT \'de\' NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, email CLOB NOT NULL, password CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_450D96CDE7927C74 ON construction_manager (email)');
        $this->addSql('CREATE INDEX IDX_450D96CDA7F12217 ON construction_manager (active_construction_site_id)');
        $this->addSql('CREATE TABLE issue_position (id CHAR(36) NOT NULL --(DC2Type:guid)
        , issue_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , map_file_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, position_zoom_scale DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9184CE5E5E7AA58C ON issue_position (issue_id)');
        $this->addSql('CREATE INDEX IDX_9184CE5EBCE08130 ON issue_position (map_file_id)');
        $this->addSql('CREATE TABLE email (id CHAR(36) NOT NULL --(DC2Type:guid)
        , receiver CLOB NOT NULL, subject CLOB NOT NULL, body CLOB NOT NULL, action_text CLOB DEFAULT NULL, action_link CLOB DEFAULT NULL, email_type INTEGER NOT NULL, sent_date_time DATETIME DEFAULT NULL, visited_date_time DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE filter (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_site VARCHAR(255) DEFAULT NULL, is_marked BOOLEAN DEFAULT NULL, number INTEGER DEFAULT NULL, trades CLOB DEFAULT NULL --(DC2Type:simple_array)
        , craftsmen CLOB DEFAULT NULL --(DC2Type:simple_array)
        , maps CLOB DEFAULT NULL --(DC2Type:simple_array)
        , issues CLOB DEFAULT NULL --(DC2Type:simple_array)
        , registration_status BOOLEAN DEFAULT NULL, registration_start DATETIME DEFAULT NULL, registration_end DATETIME DEFAULT NULL, read_status BOOLEAN DEFAULT NULL, responded_status BOOLEAN DEFAULT NULL, responded_start DATETIME DEFAULT NULL, responded_end DATETIME DEFAULT NULL, reviewed_status BOOLEAN DEFAULT NULL, reviewed_start DATETIME DEFAULT NULL, reviewed_end DATETIME DEFAULT NULL, limit_start DATETIME DEFAULT NULL, limit_end DATETIME DEFAULT NULL, number_text CLOB DEFAULT NULL, access_identifier CLOB DEFAULT NULL, access_until DATETIME DEFAULT NULL, last_access DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE note (id CHAR(36) NOT NULL --(DC2Type:guid)
        , created_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , content CLOB NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CFBDFA14B03A8386 ON note (created_by_id)');
        $this->addSql('CREATE INDEX IDX_CFBDFA144994A532 ON note (construction_site_id)');
        $this->addSql('CREATE TABLE authentication_token (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_manager_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , token CLOB NOT NULL, last_used DATETIME NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B54C4ADDA69C9147 ON authentication_token (construction_manager_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE map');
        $this->addSql('DROP TABLE map_file');
        $this->addSql('DROP TABLE issue');
        $this->addSql('DROP TABLE issue_image');
        $this->addSql('DROP TABLE construction_site');
        $this->addSql('DROP TABLE construction_site_construction_manager');
        $this->addSql('DROP TABLE construction_site_image');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('DROP TABLE construction_manager');
        $this->addSql('DROP TABLE issue_position');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE authentication_token');
    }
}
