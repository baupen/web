<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201030162559 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE map DROP INDEX IDX_93ADAABB93CB796C, ADD UNIQUE INDEX UNIQ_93ADAABB93CB796C (file_id)');
        $this->addSql('ALTER TABLE map_file DROP FOREIGN KEY FK_1FB7918553C55F64');
        $this->addSql('DROP INDEX IDX_1FB7918553C55F64 ON map_file');
        $this->addSql('ALTER TABLE map_file DROP map_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE map DROP INDEX UNIQ_93ADAABB93CB796C, ADD INDEX IDX_93ADAABB93CB796C (file_id)');
        $this->addSql('ALTER TABLE map_file ADD map_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE map_file ADD CONSTRAINT FK_1FB7918553C55F64 FOREIGN KEY (map_id) REFERENCES map (id)');
        $this->addSql('CREATE INDEX IDX_1FB7918553C55F64 ON map_file (map_id)');
    }
}
