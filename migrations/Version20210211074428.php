<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210211074428 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE map_file DROP FOREIGN KEY FK_1FB791854994A532');
        $this->addSql('DROP INDEX IDX_1FB791854994A532 ON map_file');
        $this->addSql('ALTER TABLE map_file DROP construction_site_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE map_file ADD construction_site_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE map_file ADD CONSTRAINT FK_1FB791854994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('CREATE INDEX IDX_1FB791854994A532 ON map_file (construction_site_id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
