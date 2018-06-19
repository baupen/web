<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180619144755 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE construction_manager (id CHAR(36) NOT NULL --(DC2Type:guid)
        , given_name CLOB DEFAULT NULL, family_name CLOB DEFAULT NULL, phone CLOB DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_450D96CDE7927C74 ON construction_manager (email)');
        $this->addSql('CREATE TABLE map (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , parent_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name CLOB NOT NULL, filename CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_93ADAABB4994A532 ON map (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_93ADAABB727ACA70 ON map (parent_id)');
        $this->addSql('CREATE TABLE email (id CHAR(36) NOT NULL --(DC2Type:guid)
        , receiver CLOB NOT NULL, identifier CLOB NOT NULL, subject CLOB NOT NULL, body CLOB NOT NULL, action_text CLOB DEFAULT NULL, action_link CLOB DEFAULT NULL, carbon_copy CLOB DEFAULT NULL, email_type INTEGER NOT NULL, sent_date_time DATETIME NOT NULL, visited_date_time DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE craftsman (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_site_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , name CLOB NOT NULL, trade CLOB NOT NULL, email CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7DC593834994A532 ON craftsman (construction_site_id)');
        $this->addSql('CREATE TABLE construction_site (id CHAR(36) NOT NULL --(DC2Type:guid)
        , name CLOB NOT NULL, image_file_name CLOB DEFAULT NULL, street_address CLOB DEFAULT NULL, postal_code INTEGER DEFAULT NULL, locality CLOB DEFAULT NULL, country CLOB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE buildings_construction_managers (construction_site_id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_manager_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(construction_site_id, construction_manager_id))');
        $this->addSql('CREATE INDEX IDX_5686F37B4994A532 ON buildings_construction_managers (construction_site_id)');
        $this->addSql('CREATE INDEX IDX_5686F37BA69C9147 ON buildings_construction_managers (construction_manager_id)');
        $this->addSql('CREATE TABLE authentication_token (id CHAR(36) NOT NULL --(DC2Type:guid)
        , construction_manager_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , token CLOB NOT NULL, last_used DATETIME NOT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B54C4ADDA69C9147 ON authentication_token (construction_manager_id)');
        $this->addSql('CREATE TABLE issue (id CHAR(36) NOT NULL --(DC2Type:guid)
        , upload_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , registration_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , response_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , review_by_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , craftsman_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , number INTEGER DEFAULT NULL, is_marked BOOLEAN NOT NULL, was_added_with_client BOOLEAN NOT NULL, description CLOB DEFAULT NULL, image_filename CLOB DEFAULT NULL, uploaded_at DATETIME NOT NULL, registered_at DATETIME DEFAULT NULL, responded_at DATETIME DEFAULT NULL, reviewed_at DATETIME DEFAULT NULL, position_x DOUBLE PRECISION DEFAULT NULL, position_y DOUBLE PRECISION DEFAULT NULL, position_zoom_scale DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, last_changed_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_12AD233E83BA6D1B ON issue (upload_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E254D80EC ON issue (registration_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EBA91FB54 ON issue (response_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EB9690C1F ON issue (review_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E34508F72 ON issue (craftsman_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE construction_manager');
        $this->addSql('DROP TABLE map');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE craftsman');
        $this->addSql('DROP TABLE construction_site');
        $this->addSql('DROP TABLE buildings_construction_managers');
        $this->addSql('DROP TABLE authentication_token');
        $this->addSql('DROP TABLE issue');
    }
}
