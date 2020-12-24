<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201222072909 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE craftsman ADD last_email_received DATETIME DEFAULT NULL, ADD last_visit_online DATETIME DEFAULT NULL, DROP last_email_sent, DROP last_online_visit');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE craftsman ADD last_email_sent DATETIME DEFAULT NULL, ADD last_online_visit DATETIME DEFAULT NULL, DROP last_email_received, DROP last_visit_online');
    }
}
