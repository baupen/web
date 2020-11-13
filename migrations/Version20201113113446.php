<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201113113446 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter ADD state INT DEFAULT NULL, ADD registered_at_after DATETIME DEFAULT NULL, ADD registered_at_before DATETIME DEFAULT NULL, ADD responded_at_after DATETIME DEFAULT NULL, ADD responded_at_before DATETIME DEFAULT NULL, ADD reviewed_at_after DATETIME DEFAULT NULL, ADD reviewed_at_before DATETIME DEFAULT NULL, ADD deadline_at_before DATETIME DEFAULT NULL, ADD deadline_at_after DATETIME DEFAULT NULL, ADD craftsman_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ADD craftsman_trades LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ADD map_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE issue CHANGE response_limit deadline DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter DROP state, DROP registered_at_after, DROP registered_at_before, DROP responded_at_after, DROP responded_at_before, DROP reviewed_at_after, DROP reviewed_at_before, DROP deadline_at_before, DROP deadline_at_after, DROP craftsman_ids, DROP craftsman_trades, DROP map_ids');
        $this->addSql('ALTER TABLE issue CHANGE deadline response_limit DATETIME DEFAULT NULL');
    }
}
