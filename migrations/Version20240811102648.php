<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811102648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', construction_site_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', closed_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', description LONGTEXT NOT NULL, deadline DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, closed_at DATETIME DEFAULT NULL, INDEX IDX_527EDB254994A532 (construction_site_id), INDEX IDX_527EDB25B03A8386 (created_by_id), INDEX IDX_527EDB25E1FA7797 (closed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB254994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25B03A8386 FOREIGN KEY (created_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25E1FA7797 FOREIGN KEY (closed_by_id) REFERENCES construction_manager (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE task');
    }
}
