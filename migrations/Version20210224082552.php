<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210224082552 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter ADD was_added_with_client TINYINT(1) DEFAULT NULL, ADD description LONGTEXT DEFAULT NULL, ADD created_at_after DATETIME DEFAULT NULL, ADD created_at_before DATETIME DEFAULT NULL, CHANGE craftsman_trades numbers LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter DROP was_added_with_client, DROP description, DROP created_at_after, DROP created_at_before, CHANGE numbers craftsman_trades LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\'');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
