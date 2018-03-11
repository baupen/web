<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180311102902 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE building_map (id INTEGER NOT NULL, building_id INTEGER DEFAULT NULL, file_name CLOB NOT NULL, guid VARCHAR(255) NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B8D991E4D2A7E12 ON building_map (building_id)');
        $this->addSql('CREATE TABLE frontend_user (id INTEGER NOT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEAE7927C74 ON frontend_user (email)');
        $this->addSql('CREATE TABLE setting (id INTEGER NOT NULL, frontend_user_id INTEGER DEFAULT NULL, "key" CLOB NOT NULL, content CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9F74B8987887A021 ON setting (frontend_user_id)');
        $this->addSql('CREATE TABLE app_user (id INTEGER NOT NULL, identifier CLOB NOT NULL, password_hash CLOB NOT NULL, authentication_token CLOB NOT NULL, guid VARCHAR(255) NOT NULL, given_name CLOB NOT NULL, family_name CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE app_user_buildings (app_user_id INTEGER NOT NULL, building_id INTEGER NOT NULL, PRIMARY KEY(app_user_id, building_id))');
        $this->addSql('CREATE INDEX IDX_454BA9BD4A3353D8 ON app_user_buildings (app_user_id)');
        $this->addSql('CREATE INDEX IDX_454BA9BD4D2A7E12 ON app_user_buildings (building_id)');
        $this->addSql('CREATE TABLE email (id INTEGER NOT NULL, receiver CLOB NOT NULL, identifier CLOB NOT NULL, subject CLOB NOT NULL, body CLOB NOT NULL, action_text CLOB DEFAULT NULL, action_link CLOB DEFAULT NULL, carbon_copy CLOB DEFAULT NULL, email_type INTEGER NOT NULL, sent_date_time DATETIME NOT NULL, visited_date_time DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE craftsman (id INTEGER NOT NULL, guid VARCHAR(255) NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, email CLOB NOT NULL, webpage CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE marker (id INTEGER NOT NULL, craftsman_id INTEGER DEFAULT NULL, building_map_id INTEGER DEFAULT NULL, created_by_id INTEGER DEFAULT NULL, status INTEGER NOT NULL, mark_xpercentage DOUBLE PRECISION NOT NULL, mark_ypercentage DOUBLE PRECISION NOT NULL, frame_xpercentage DOUBLE PRECISION NOT NULL, frame_ypercentage DOUBLE PRECISION NOT NULL, frame_xheight DOUBLE PRECISION NOT NULL, frame_ylength DOUBLE PRECISION NOT NULL, content CLOB NOT NULL, image_file_name CLOB NOT NULL, guid VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, last_changed_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82CF20FE34508F72 ON marker (craftsman_id)');
        $this->addSql('CREATE INDEX IDX_82CF20FE4373858F ON marker (building_map_id)');
        $this->addSql('CREATE INDEX IDX_82CF20FEB03A8386 ON marker (created_by_id)');
        $this->addSql('CREATE TABLE backend_user (id INTEGER NOT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C73586EE7927C74 ON backend_user (email)');
        $this->addSql('CREATE TABLE building (id INTEGER NOT NULL, guid VARCHAR(255) NOT NULL, name CLOB NOT NULL, description CLOB DEFAULT NULL, street CLOB DEFAULT NULL, street_nr CLOB DEFAULT NULL, address_line CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, city CLOB DEFAULT NULL, country CLOB DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE building_map');
        $this->addSql('DROP TABLE frontend_user');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE app_user_buildings');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('DROP TABLE marker');
        $this->addSql('DROP TABLE backend_user');
        $this->addSql('DROP TABLE building');
    }
}
