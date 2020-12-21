<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201220182606 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EBCE08130');
        $this->addSql('DROP INDEX IDX_12AD233EBCE08130 ON issue');
        $this->addSql('ALTER TABLE issue DROP map_file_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue ADD map_file_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EBCE08130 FOREIGN KEY (map_file_id) REFERENCES map_file (id)');
        $this->addSql('CREATE INDEX IDX_12AD233EBCE08130 ON issue (map_file_id)');
    }
}
