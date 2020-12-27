<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201227085608 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_template CHANGE purpose purpose INT DEFAULT NULL');
        $this->addSql('ALTER TABLE filter CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_changed_at last_changed_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_template CHANGE purpose purpose INT NOT NULL');
        $this->addSql('ALTER TABLE filter CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE last_changed_at last_changed_at DATETIME DEFAULT NULL');
    }
}
