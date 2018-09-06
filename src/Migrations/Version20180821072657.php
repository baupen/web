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
final class Version20180821072657 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX UNIQ_450D96CDE7927C74');
        $this->addSql('DROP INDEX IDX_450D96CDA7F12217');
        $this->addSql('CREATE TEMPORARY TABLE __temp__construction_manager AS SELECT id, active_construction_site_id, given_name, family_name, phone, created_at, last_changed_at, email, password, password_hash, reset_hash, is_enabled, registration_date, agb_accepted FROM construction_manager');
        $this->addSql('DROP TABLE construction_manager');
        $this->addSql('CREATE TABLE construction_manager (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , active_construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , given_name CLOB DEFAULT NULL COLLATE BINARY, family_name CLOB DEFAULT NULL COLLATE BINARY, phone CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, email CLOB NOT NULL COLLATE BINARY, password CLOB NOT NULL COLLATE BINARY, password_hash CLOB NOT NULL COLLATE BINARY, reset_hash CLOB NOT NULL COLLATE BINARY, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, locale CLOB DEFAULT \'de\' NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_450D96CDA7F12217 FOREIGN KEY (active_construction_site_id) REFERENCES construction_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO construction_manager (id, active_construction_site_id, given_name, family_name, phone, created_at, last_changed_at, email, password, password_hash, reset_hash, is_enabled, registration_date, agb_accepted) SELECT id, active_construction_site_id, given_name, family_name, phone, created_at, last_changed_at, email, password, password_hash, reset_hash, is_enabled, registration_date, agb_accepted FROM __temp__construction_manager');
        $this->addSql('DROP TABLE __temp__construction_manager');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_450D96CDE7927C74 ON construction_manager (email)');
        $this->addSql('CREATE INDEX IDX_450D96CDA7F12217 ON construction_manager (active_construction_site_id)');
        $this->addSql('DROP INDEX IDX_93ADAABB4994A532');
        $this->addSql('DROP INDEX IDX_93ADAABB727ACA70');
        $this->addSql('CREATE TEMPORARY TABLE __temp__map AS SELECT id, construction_site_id, parent_id, name, filename, created_at, last_changed_at FROM map');
        $this->addSql('DROP TABLE map');
        $this->addSql('CREATE TABLE map (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , parent_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name CLOB NOT NULL COLLATE BINARY, filename CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_93ADAABB4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_93ADAABB727ACA70 FOREIGN KEY (parent_id) REFERENCES map (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO map (id, construction_site_id, parent_id, name, filename, created_at, last_changed_at) SELECT id, construction_site_id, parent_id, name, filename, created_at, last_changed_at FROM __temp__map');
        $this->addSql('DROP TABLE __temp__map');
        $this->addSql('CREATE INDEX IDX_93ADAABB4994A532 ON map (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_93ADAABB727ACA70 ON map (parent_id)');
        $this->addSql('DROP INDEX IDX_7DC593834994A532');
        $this->addSql('DROP INDEX IDX_7DC59383AD2B52E5');
        $this->addSql('CREATE TEMPORARY TABLE __temp__craftsman AS SELECT id, construction_site_id, share_view_filter_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country, last_email_sent, last_online_visit, email_identifier FROM craftsman');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('CREATE TABLE craftsman (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , share_view_filter_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , contact_name CLOB NOT NULL COLLATE BINARY, company CLOB NOT NULL COLLATE BINARY, trade CLOB NOT NULL COLLATE BINARY, email CLOB NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address CLOB DEFAULT NULL COLLATE BINARY, postal_code INTEGER DEFAULT NULL, locality CLOB DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, last_email_sent DATETIME DEFAULT NULL, last_online_visit DATETIME DEFAULT NULL, email_identifier CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_7DC593834994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7DC59383AD2B52E5 FOREIGN KEY (share_view_filter_id) REFERENCES filter (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO craftsman (id, construction_site_id, share_view_filter_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country, last_email_sent, last_online_visit, email_identifier) SELECT id, construction_site_id, share_view_filter_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country, last_email_sent, last_online_visit, email_identifier FROM __temp__craftsman');
        $this->addSql('DROP TABLE __temp__craftsman');
        $this->addSql('CREATE INDEX IDX_7DC593834994A532 ON craftsman (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_7DC59383AD2B52E5 ON craftsman (share_view_filter_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__filter AS SELECT id, is_marked, number, responded_status, responded_start, responded_end, reviewed_status, reviewed_start, reviewed_end, limit_start, limit_end, construction_site, registration_status, registration_start, registration_end, read_status, number_text, access_until, access_identifier, last_access, trades, craftsmen, maps, issues FROM filter');
        $this->addSql('DROP TABLE filter');
        $this->addSql('CREATE TABLE filter (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , is_marked BOOLEAN DEFAULT NULL, number INTEGER DEFAULT NULL, responded_status BOOLEAN DEFAULT NULL, responded_start DATETIME DEFAULT NULL, responded_end DATETIME DEFAULT NULL, reviewed_status BOOLEAN DEFAULT NULL, reviewed_start DATETIME DEFAULT NULL, reviewed_end DATETIME DEFAULT NULL, limit_start DATETIME DEFAULT NULL, limit_end DATETIME DEFAULT NULL, construction_site VARCHAR(255) DEFAULT NULL COLLATE BINARY, registration_status BOOLEAN DEFAULT NULL, registration_start DATETIME DEFAULT NULL, registration_end DATETIME DEFAULT NULL, read_status BOOLEAN DEFAULT NULL, number_text CLOB DEFAULT NULL COLLATE BINARY, access_until DATETIME DEFAULT NULL, access_identifier CLOB DEFAULT NULL COLLATE BINARY, last_access DATETIME DEFAULT NULL, trades CLOB DEFAULT NULL --(DC2Type:simple_array)
        , craftsmen CLOB DEFAULT NULL --(DC2Type:simple_array)
        , maps CLOB DEFAULT NULL --(DC2Type:simple_array)
        , issues CLOB DEFAULT NULL --(DC2Type:simple_array)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO filter (id, is_marked, number, responded_status, responded_start, responded_end, reviewed_status, reviewed_start, reviewed_end, limit_start, limit_end, construction_site, registration_status, registration_start, registration_end, read_status, number_text, access_until, access_identifier, last_access, trades, craftsmen, maps, issues) SELECT id, is_marked, number, responded_status, responded_start, responded_end, reviewed_status, reviewed_start, reviewed_end, limit_start, limit_end, construction_site, registration_status, registration_start, registration_end, read_status, number_text, access_until, access_identifier, last_access, trades, craftsmen, maps, issues FROM __temp__filter');
        $this->addSql('DROP TABLE __temp__filter');
        $this->addSql('DROP INDEX IDX_CFBDFA144994A532');
        $this->addSql('DROP INDEX IDX_CFBDFA14B03A8386');
        $this->addSql('CREATE TEMPORARY TABLE __temp__note AS SELECT id, created_by_id, construction_site_id, content, created_at, last_changed_at FROM note');
        $this->addSql('DROP TABLE note');
        $this->addSql('CREATE TABLE note (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , created_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , content CLOB NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_CFBDFA14B03A8386 FOREIGN KEY (created_by_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_CFBDFA144994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO note (id, created_by_id, construction_site_id, content, created_at, last_changed_at) SELECT id, created_by_id, construction_site_id, content, created_at, last_changed_at FROM __temp__note');
        $this->addSql('DROP TABLE __temp__note');
        $this->addSql('CREATE INDEX IDX_CFBDFA144994A532 ON note (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_CFBDFA14B03A8386 ON note (created_by_id)');
        $this->addSql('DROP INDEX IDX_BC4D21F04994A532');
        $this->addSql('DROP INDEX IDX_BC4D21F0A69C9147');
        $this->addSql('CREATE TEMPORARY TABLE __temp__construction_site_construction_manager AS SELECT construction_site_id, construction_manager_id FROM construction_site_construction_manager');
        $this->addSql('DROP TABLE construction_site_construction_manager');
        $this->addSql('CREATE TABLE construction_site_construction_manager (construction_site_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , construction_manager_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(construction_site_id, construction_manager_id), CONSTRAINT FK_BC4D21F04994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC4D21F0A69C9147 FOREIGN KEY (construction_manager_id) REFERENCES construction_manager (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO construction_site_construction_manager (construction_site_id, construction_manager_id) SELECT construction_site_id, construction_manager_id FROM __temp__construction_site_construction_manager');
        $this->addSql('DROP TABLE __temp__construction_site_construction_manager');
        $this->addSql('CREATE INDEX IDX_BC4D21F04994A532 ON construction_site_construction_manager (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_BC4D21F0A69C9147 ON construction_site_construction_manager (construction_manager_id)');
        $this->addSql('DROP INDEX IDX_B54C4ADDA69C9147');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authentication_token AS SELECT id, construction_manager_id, token, last_used, created_at, last_changed_at FROM authentication_token');
        $this->addSql('DROP TABLE authentication_token');
        $this->addSql('CREATE TABLE authentication_token (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , construction_manager_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , token CLOB NOT NULL COLLATE BINARY, last_used DATETIME NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_B54C4ADDA69C9147 FOREIGN KEY (construction_manager_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO authentication_token (id, construction_manager_id, token, last_used, created_at, last_changed_at) SELECT id, construction_manager_id, token, last_used, created_at, last_changed_at FROM __temp__authentication_token');
        $this->addSql('DROP TABLE __temp__authentication_token');
        $this->addSql('CREATE INDEX IDX_B54C4ADDA69C9147 ON authentication_token (construction_manager_id)');
        $this->addSql('DROP INDEX IDX_12AD233E83BA6D1B');
        $this->addSql('DROP INDEX IDX_12AD233E254D80EC');
        $this->addSql('DROP INDEX IDX_12AD233EBA91FB54');
        $this->addSql('DROP INDEX IDX_12AD233EB9690C1F');
        $this->addSql('DROP INDEX IDX_12AD233E34508F72');
        $this->addSql('DROP INDEX IDX_12AD233E53C55F64');
        $this->addSql('CREATE TEMPORARY TABLE __temp__issue AS SELECT id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at, response_limit FROM issue');
        $this->addSql('DROP TABLE issue');
        $this->addSql('CREATE TABLE issue (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , upload_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , registration_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , response_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , review_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , craftsman_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , map_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , number INTEGER DEFAULT NULL, is_marked BOOLEAN NOT NULL, was_added_with_client BOOLEAN NOT NULL, description CLOB DEFAULT NULL COLLATE BINARY, image_filename CLOB DEFAULT NULL COLLATE BINARY, uploaded_at DATETIME NOT NULL, registered_at DATETIME DEFAULT NULL, responded_at DATETIME DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, position_zoom_scale DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, response_limit DATETIME DEFAULT NULL, PRIMARY KEY(id), CONSTRAINT FK_12AD233E83BA6D1B FOREIGN KEY (upload_by_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233E254D80EC FOREIGN KEY (registration_by_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233EBA91FB54 FOREIGN KEY (response_by_id) REFERENCES craftsman (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233EB9690C1F FOREIGN KEY (review_by_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233E34508F72 FOREIGN KEY (craftsman_id) REFERENCES craftsman (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233E53C55F64 FOREIGN KEY (map_id) REFERENCES map (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO issue (id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at, response_limit) SELECT id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at, response_limit FROM __temp__issue');
        $this->addSql('DROP TABLE __temp__issue');
        $this->addSql('CREATE INDEX IDX_12AD233E83BA6D1B ON issue (upload_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E254D80EC ON issue (registration_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EBA91FB54 ON issue (response_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EB9690C1F ON issue (review_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E34508F72 ON issue (craftsman_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E53C55F64 ON issue (map_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_B54C4ADDA69C9147');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authentication_token AS SELECT id, construction_manager_id, token, last_used, created_at, last_changed_at FROM authentication_token');
        $this->addSql('DROP TABLE authentication_token');
        $this->addSql('CREATE TABLE authentication_token (id CHAR(36) NOT NULL --(DC2Type:guid)
        , token CLOB NOT NULL, last_used DATETIME NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, construction_manager_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO authentication_token (id, construction_manager_id, token, last_used, created_at, last_changed_at) SELECT id, construction_manager_id, token, last_used, created_at, last_changed_at FROM __temp__authentication_token');
        $this->addSql('DROP TABLE __temp__authentication_token');
        $this->addSql('CREATE INDEX IDX_B54C4ADDA69C9147 ON authentication_token (construction_manager_id)');
        $this->addSql('DROP INDEX UNIQ_450D96CDE7927C74');
        $this->addSql('DROP INDEX IDX_450D96CDA7F12217');
        $this->addSql('CREATE TEMPORARY TABLE __temp__construction_manager AS SELECT id, active_construction_site_id, given_name, family_name, phone, created_at, last_changed_at, email, password, password_hash, reset_hash, is_enabled, registration_date, agb_accepted FROM construction_manager');
        $this->addSql('DROP TABLE construction_manager');
        $this->addSql('CREATE TABLE construction_manager (id CHAR(36) NOT NULL --(DC2Type:guid)
        , given_name CLOB DEFAULT NULL, family_name CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, email CLOB NOT NULL, password CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, active_construction_site_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO construction_manager (id, active_construction_site_id, given_name, family_name, phone, created_at, last_changed_at, email, password, password_hash, reset_hash, is_enabled, registration_date, agb_accepted) SELECT id, active_construction_site_id, given_name, family_name, phone, created_at, last_changed_at, email, password, password_hash, reset_hash, is_enabled, registration_date, agb_accepted FROM __temp__construction_manager');
        $this->addSql('DROP TABLE __temp__construction_manager');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_450D96CDE7927C74 ON construction_manager (email)');
        $this->addSql('CREATE INDEX IDX_450D96CDA7F12217 ON construction_manager (active_construction_site_id)');
        $this->addSql('DROP INDEX IDX_BC4D21F04994A532');
        $this->addSql('DROP INDEX IDX_BC4D21F0A69C9147');
        $this->addSql('CREATE TEMPORARY TABLE __temp__construction_site_construction_manager AS SELECT construction_site_id, construction_manager_id FROM construction_site_construction_manager');
        $this->addSql('DROP TABLE construction_site_construction_manager');
        $this->addSql('CREATE TABLE construction_site_construction_manager (construction_site_id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_manager_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(construction_site_id, construction_manager_id))');
        $this->addSql('INSERT INTO construction_site_construction_manager (construction_site_id, construction_manager_id) SELECT construction_site_id, construction_manager_id FROM __temp__construction_site_construction_manager');
        $this->addSql('DROP TABLE __temp__construction_site_construction_manager');
        $this->addSql('CREATE INDEX IDX_BC4D21F04994A532 ON construction_site_construction_manager (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_BC4D21F0A69C9147 ON construction_site_construction_manager (construction_manager_id)');
        $this->addSql('DROP INDEX IDX_7DC593834994A532');
        $this->addSql('DROP INDEX IDX_7DC59383AD2B52E5');
        $this->addSql('CREATE TEMPORARY TABLE __temp__craftsman AS SELECT id, construction_site_id, share_view_filter_id, contact_name, company, trade, email, last_email_sent, last_online_visit, email_identifier, created_at, last_changed_at, street_address, postal_code, locality, country FROM craftsman');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('CREATE TABLE craftsman (id CHAR(36) NOT NULL --(DC2Type:guid)
        , contact_name CLOB NOT NULL, company CLOB NOT NULL, trade CLOB NOT NULL, email CLOB NOT NULL, last_email_sent DATETIME DEFAULT NULL, last_online_visit DATETIME DEFAULT NULL, email_identifier CLOB NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, locality CLOB DEFAULT NULL, country CLOB DEFAULT NULL, construction_site_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , share_view_filter_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO craftsman (id, construction_site_id, share_view_filter_id, contact_name, company, trade, email, last_email_sent, last_online_visit, email_identifier, created_at, last_changed_at, street_address, postal_code, locality, country) SELECT id, construction_site_id, share_view_filter_id, contact_name, company, trade, email, last_email_sent, last_online_visit, email_identifier, created_at, last_changed_at, street_address, postal_code, locality, country FROM __temp__craftsman');
        $this->addSql('DROP TABLE __temp__craftsman');
        $this->addSql('CREATE INDEX IDX_7DC593834994A532 ON craftsman (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_7DC59383AD2B52E5 ON craftsman (share_view_filter_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__filter AS SELECT id, construction_site, is_marked, number, trades, craftsmen, maps, issues, registration_status, registration_start, registration_end, read_status, responded_status, responded_start, responded_end, reviewed_status, reviewed_start, reviewed_end, limit_start, limit_end, number_text, access_identifier, access_until, last_access FROM filter');
        $this->addSql('DROP TABLE filter');
        $this->addSql('CREATE TABLE filter (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_site VARCHAR(255) DEFAULT NULL, is_marked BOOLEAN DEFAULT NULL, number INTEGER DEFAULT NULL, registration_status BOOLEAN DEFAULT NULL, registration_start DATETIME DEFAULT NULL, registration_end DATETIME DEFAULT NULL, read_status BOOLEAN DEFAULT NULL, responded_status BOOLEAN DEFAULT NULL, responded_start DATETIME DEFAULT NULL, responded_end DATETIME DEFAULT NULL, reviewed_status BOOLEAN DEFAULT NULL, reviewed_start DATETIME DEFAULT NULL, reviewed_end DATETIME DEFAULT NULL, limit_start DATETIME DEFAULT NULL, limit_end DATETIME DEFAULT NULL, number_text CLOB DEFAULT NULL, access_identifier CLOB DEFAULT NULL, access_until DATETIME DEFAULT NULL, last_access DATETIME DEFAULT NULL, trades CLOB DEFAULT \'NULL --(DC2Type:simple_array)\' COLLATE BINARY --(DC2Type:simple_array)
        , craftsmen CLOB DEFAULT \'NULL --(DC2Type:simple_array)\' COLLATE BINARY --(DC2Type:simple_array)
        , maps CLOB DEFAULT \'NULL --(DC2Type:simple_array)\' COLLATE BINARY --(DC2Type:simple_array)
        , issues CLOB DEFAULT \'NULL --(DC2Type:simple_array)\' COLLATE BINARY --(DC2Type:simple_array)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO filter (id, construction_site, is_marked, number, trades, craftsmen, maps, issues, registration_status, registration_start, registration_end, read_status, responded_status, responded_start, responded_end, reviewed_status, reviewed_start, reviewed_end, limit_start, limit_end, number_text, access_identifier, access_until, last_access) SELECT id, construction_site, is_marked, number, trades, craftsmen, maps, issues, registration_status, registration_start, registration_end, read_status, responded_status, responded_start, responded_end, reviewed_status, reviewed_start, reviewed_end, limit_start, limit_end, number_text, access_identifier, access_until, last_access FROM __temp__filter');
        $this->addSql('DROP TABLE __temp__filter');
        $this->addSql('DROP INDEX IDX_12AD233E83BA6D1B');
        $this->addSql('DROP INDEX IDX_12AD233E254D80EC');
        $this->addSql('DROP INDEX IDX_12AD233EBA91FB54');
        $this->addSql('DROP INDEX IDX_12AD233EB9690C1F');
        $this->addSql('DROP INDEX IDX_12AD233E34508F72');
        $this->addSql('DROP INDEX IDX_12AD233E53C55F64');
        $this->addSql('CREATE TEMPORARY TABLE __temp__issue AS SELECT id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, response_limit, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at FROM issue');
        $this->addSql('DROP TABLE issue');
        $this->addSql('CREATE TABLE issue (id CHAR(36) NOT NULL --(DC2Type:guid)
        , number INTEGER DEFAULT NULL, is_marked BOOLEAN NOT NULL, was_added_with_client BOOLEAN NOT NULL, description CLOB DEFAULT NULL, image_filename CLOB DEFAULT NULL, response_limit DATETIME DEFAULT NULL, uploaded_at DATETIME NOT NULL, registered_at DATETIME DEFAULT NULL, responded_at DATETIME DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, position_zoom_scale DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, upload_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , registration_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , response_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , review_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , craftsman_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , map_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO issue (id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, response_limit, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at) SELECT id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, response_limit, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at FROM __temp__issue');
        $this->addSql('DROP TABLE __temp__issue');
        $this->addSql('CREATE INDEX IDX_12AD233E83BA6D1B ON issue (upload_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E254D80EC ON issue (registration_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EBA91FB54 ON issue (response_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EB9690C1F ON issue (review_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E34508F72 ON issue (craftsman_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E53C55F64 ON issue (map_id)');
        $this->addSql('DROP INDEX IDX_93ADAABB4994A532');
        $this->addSql('DROP INDEX IDX_93ADAABB727ACA70');
        $this->addSql('CREATE TEMPORARY TABLE __temp__map AS SELECT id, construction_site_id, parent_id, name, filename, created_at, last_changed_at FROM map');
        $this->addSql('DROP TABLE map');
        $this->addSql('CREATE TABLE map (id CHAR(36) NOT NULL --(DC2Type:guid)
        , name CLOB NOT NULL, filename CLOB DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, construction_site_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , parent_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO map (id, construction_site_id, parent_id, name, filename, created_at, last_changed_at) SELECT id, construction_site_id, parent_id, name, filename, created_at, last_changed_at FROM __temp__map');
        $this->addSql('DROP TABLE __temp__map');
        $this->addSql('CREATE INDEX IDX_93ADAABB4994A532 ON map (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_93ADAABB727ACA70 ON map (parent_id)');
        $this->addSql('DROP INDEX IDX_CFBDFA14B03A8386');
        $this->addSql('DROP INDEX IDX_CFBDFA144994A532');
        $this->addSql('CREATE TEMPORARY TABLE __temp__note AS SELECT id, created_by_id, construction_site_id, content, created_at, last_changed_at FROM note');
        $this->addSql('DROP TABLE note');
        $this->addSql('CREATE TABLE note (id CHAR(36) NOT NULL --(DC2Type:guid)
        , content CLOB NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, created_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO note (id, created_by_id, construction_site_id, content, created_at, last_changed_at) SELECT id, created_by_id, construction_site_id, content, created_at, last_changed_at FROM __temp__note');
        $this->addSql('DROP TABLE __temp__note');
        $this->addSql('CREATE INDEX IDX_CFBDFA14B03A8386 ON note (created_by_id)');
        $this->addSql('CREATE INDEX IDX_CFBDFA144994A532 ON note (construction_site_id)');
    }
}
