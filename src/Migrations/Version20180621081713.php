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
final class Version20180621081713 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_93ADAABB727ACA70');
        $this->addSql('DROP INDEX IDX_93ADAABB4994A532');
        $this->addSql('CREATE TEMPORARY TABLE __temp__map AS SELECT id, construction_site_id, parent_id, name, filename, created_at, last_changed_at FROM map');
        $this->addSql('DROP TABLE map');
        $this->addSql('CREATE TABLE map (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , parent_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name CLOB NOT NULL COLLATE BINARY, filename CLOB DEFAULT NULL COLLATE BINARY, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_93ADAABB4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_93ADAABB727ACA70 FOREIGN KEY (parent_id) REFERENCES map (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO map (id, construction_site_id, parent_id, name, filename, created_at, last_changed_at) SELECT id, construction_site_id, parent_id, name, filename, created_at, last_changed_at FROM __temp__map');
        $this->addSql('DROP TABLE __temp__map');
        $this->addSql('CREATE INDEX IDX_93ADAABB727ACA70 ON map (parent_id)');
        $this->addSql('CREATE INDEX IDX_93ADAABB4994A532 ON map (construction_site_id)');
        $this->addSql('DROP INDEX IDX_7DC593834994A532');
        $this->addSql('CREATE TEMPORARY TABLE __temp__craftsman AS SELECT id, construction_site_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country FROM craftsman');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('CREATE TABLE craftsman (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , contact_name CLOB NOT NULL COLLATE BINARY, company CLOB NOT NULL COLLATE BINARY, trade CLOB NOT NULL COLLATE BINARY, email CLOB NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address CLOB DEFAULT NULL COLLATE BINARY, postal_code INTEGER DEFAULT NULL, locality CLOB DEFAULT NULL COLLATE BINARY, country CLOB DEFAULT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_7DC593834994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO craftsman (id, construction_site_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country) SELECT id, construction_site_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country FROM __temp__craftsman');
        $this->addSql('DROP TABLE __temp__craftsman');
        $this->addSql('CREATE INDEX IDX_7DC593834994A532 ON craftsman (construction_site_id)');
        $this->addSql('DROP INDEX IDX_BC4D21F0A69C9147');
        $this->addSql('DROP INDEX IDX_BC4D21F04994A532');
        $this->addSql('CREATE TEMPORARY TABLE __temp__construction_site_construction_manager AS SELECT construction_site_id, construction_manager_id FROM construction_site_construction_manager');
        $this->addSql('DROP TABLE construction_site_construction_manager');
        $this->addSql('CREATE TABLE construction_site_construction_manager (construction_site_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , construction_manager_id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(construction_site_id, construction_manager_id), CONSTRAINT FK_BC4D21F04994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_BC4D21F0A69C9147 FOREIGN KEY (construction_manager_id) REFERENCES construction_manager (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO construction_site_construction_manager (construction_site_id, construction_manager_id) SELECT construction_site_id, construction_manager_id FROM __temp__construction_site_construction_manager');
        $this->addSql('DROP TABLE __temp__construction_site_construction_manager');
        $this->addSql('CREATE INDEX IDX_BC4D21F0A69C9147 ON construction_site_construction_manager (construction_manager_id)');
        $this->addSql('CREATE INDEX IDX_BC4D21F04994A532 ON construction_site_construction_manager (construction_site_id)');
        $this->addSql('DROP INDEX IDX_B54C4ADDA69C9147');
        $this->addSql('CREATE TEMPORARY TABLE __temp__authentication_token AS SELECT id, construction_manager_id, token, last_used, created_at, last_changed_at FROM authentication_token');
        $this->addSql('DROP TABLE authentication_token');
        $this->addSql('CREATE TABLE authentication_token (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , construction_manager_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , token CLOB NOT NULL COLLATE BINARY, last_used DATETIME NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_B54C4ADDA69C9147 FOREIGN KEY (construction_manager_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO authentication_token (id, construction_manager_id, token, last_used, created_at, last_changed_at) SELECT id, construction_manager_id, token, last_used, created_at, last_changed_at FROM __temp__authentication_token');
        $this->addSql('DROP TABLE __temp__authentication_token');
        $this->addSql('CREATE INDEX IDX_B54C4ADDA69C9147 ON authentication_token (construction_manager_id)');
        $this->addSql('DROP INDEX IDX_12AD233E53C55F64');
        $this->addSql('DROP INDEX IDX_12AD233E34508F72');
        $this->addSql('DROP INDEX IDX_12AD233EB9690C1F');
        $this->addSql('DROP INDEX IDX_12AD233EBA91FB54');
        $this->addSql('DROP INDEX IDX_12AD233E254D80EC');
        $this->addSql('DROP INDEX IDX_12AD233E83BA6D1B');
        $this->addSql('CREATE TEMPORARY TABLE __temp__issue AS SELECT id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at FROM issue');
        $this->addSql('DROP TABLE issue');
        $this->addSql('CREATE TABLE issue (id CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:guid)
        , upload_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , registration_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , response_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , review_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , craftsman_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , map_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , number INTEGER DEFAULT NULL, is_marked BOOLEAN NOT NULL, was_added_with_client BOOLEAN NOT NULL, description CLOB DEFAULT NULL COLLATE BINARY, image_filename CLOB DEFAULT NULL COLLATE BINARY, uploaded_at DATETIME NOT NULL, registered_at DATETIME DEFAULT NULL, responded_at DATETIME DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, position_zoom_scale DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_12AD233E83BA6D1B FOREIGN KEY (upload_by_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233E254D80EC FOREIGN KEY (registration_by_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233EBA91FB54 FOREIGN KEY (response_by_id) REFERENCES craftsman (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233EB9690C1F FOREIGN KEY (review_by_id) REFERENCES construction_manager (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233E34508F72 FOREIGN KEY (craftsman_id) REFERENCES craftsman (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_12AD233E53C55F64 FOREIGN KEY (map_id) REFERENCES map (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO issue (id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at) SELECT id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at FROM __temp__issue');
        $this->addSql('DROP TABLE __temp__issue');
        $this->addSql('CREATE INDEX IDX_12AD233E53C55F64 ON issue (map_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E34508F72 ON issue (craftsman_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EB9690C1F ON issue (review_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EBA91FB54 ON issue (response_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E254D80EC ON issue (registration_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E83BA6D1B ON issue (upload_by_id)');
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
        $this->addSql('CREATE TEMPORARY TABLE __temp__craftsman AS SELECT id, construction_site_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country FROM craftsman');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('CREATE TABLE craftsman (id CHAR(36) NOT NULL --(DC2Type:guid)
        , contact_name CLOB NOT NULL, company CLOB NOT NULL, trade CLOB NOT NULL, email CLOB NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, street_address CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, locality CLOB DEFAULT NULL, country CLOB DEFAULT NULL, construction_site_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO craftsman (id, construction_site_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country) SELECT id, construction_site_id, contact_name, company, trade, email, created_at, last_changed_at, street_address, postal_code, locality, country FROM __temp__craftsman');
        $this->addSql('DROP TABLE __temp__craftsman');
        $this->addSql('CREATE INDEX IDX_7DC593834994A532 ON craftsman (construction_site_id)');
        $this->addSql('DROP INDEX IDX_12AD233E83BA6D1B');
        $this->addSql('DROP INDEX IDX_12AD233E254D80EC');
        $this->addSql('DROP INDEX IDX_12AD233EBA91FB54');
        $this->addSql('DROP INDEX IDX_12AD233EB9690C1F');
        $this->addSql('DROP INDEX IDX_12AD233E34508F72');
        $this->addSql('DROP INDEX IDX_12AD233E53C55F64');
        $this->addSql('CREATE TEMPORARY TABLE __temp__issue AS SELECT id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at FROM issue');
        $this->addSql('DROP TABLE issue');
        $this->addSql('CREATE TABLE issue (id CHAR(36) NOT NULL --(DC2Type:guid)
        , number INTEGER DEFAULT NULL, is_marked BOOLEAN NOT NULL, was_added_with_client BOOLEAN NOT NULL, description CLOB DEFAULT NULL, image_filename CLOB DEFAULT NULL, uploaded_at DATETIME NOT NULL, registered_at DATETIME DEFAULT NULL, responded_at DATETIME DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, position_zoom_scale DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, upload_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , registration_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , response_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , review_by_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , craftsman_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , map_id CHAR(36) DEFAULT \'NULL --(DC2Type:guid)\' COLLATE BINARY --(DC2Type:guid)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO issue (id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at) SELECT id, upload_by_id, registration_by_id, response_by_id, review_by_id, craftsman_id, map_id, number, is_marked, was_added_with_client, description, image_filename, uploaded_at, registered_at, responded_at, reviewed_at, position_x, position_y, position_zoom_scale, created_at, last_changed_at FROM __temp__issue');
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
    }
}
