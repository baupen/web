<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180222162839 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('ALTER TABLE frontend_user ADD COLUMN street CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE frontend_user ADD COLUMN street_nr CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE frontend_user ADD COLUMN address_line CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE frontend_user ADD COLUMN postal_code INTEGER DEFAULT NULL');
        $this->addSql('ALTER TABLE frontend_user ADD COLUMN city CLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE frontend_user ADD COLUMN country CLOB DEFAULT NULL');
        $this->addSql('DROP INDEX IDX_9F74B8987887A021');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, frontend_user_id, "key", content FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id INTEGER NOT NULL, frontend_user_id INTEGER DEFAULT NULL, "key" CLOB NOT NULL COLLATE BINARY, content CLOB NOT NULL COLLATE BINARY, PRIMARY KEY(id), CONSTRAINT FK_9F74B8987887A021 FOREIGN KEY (frontend_user_id) REFERENCES frontend_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO setting (id, frontend_user_id, "key", content) SELECT id, frontend_user_id, "key", content FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B8987887A021 ON setting (frontend_user_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX UNIQ_E2D1DEAE7927C74');
        $this->addSql('CREATE TEMPORARY TABLE __temp__frontend_user AS SELECT id, email, password_hash, reset_hash, is_enabled, registration_date, agb_accepted FROM frontend_user');
        $this->addSql('DROP TABLE frontend_user');
        $this->addSql('CREATE TABLE frontend_user (id INTEGER NOT NULL, email CLOB NOT NULL, password_hash CLOB NOT NULL, reset_hash CLOB NOT NULL, is_enabled BOOLEAN NOT NULL, registration_date DATETIME NOT NULL, agb_accepted BOOLEAN DEFAULT \'0\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO frontend_user (id, email, password_hash, reset_hash, is_enabled, registration_date, agb_accepted) SELECT id, email, password_hash, reset_hash, is_enabled, registration_date, agb_accepted FROM __temp__frontend_user');
        $this->addSql('DROP TABLE __temp__frontend_user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E2D1DEAE7927C74 ON frontend_user (email)');
        $this->addSql('DROP INDEX IDX_9F74B8987887A021');
        $this->addSql('CREATE TEMPORARY TABLE __temp__setting AS SELECT id, frontend_user_id, "key", content FROM setting');
        $this->addSql('DROP TABLE setting');
        $this->addSql('CREATE TABLE setting (id INTEGER NOT NULL, frontend_user_id INTEGER DEFAULT NULL, "key" CLOB NOT NULL, content CLOB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('INSERT INTO setting (id, frontend_user_id, "key", content) SELECT id, frontend_user_id, "key", content FROM __temp__setting');
        $this->addSql('DROP TABLE __temp__setting');
        $this->addSql('CREATE INDEX IDX_9F74B8987887A021 ON setting (frontend_user_id)');
    }
}
