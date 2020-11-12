<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201112053210 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter DROP public_access_identifier, DROP was_added_with_client, DROP issues, DROP any_status, DROP filter_by_issues, DROP craftsmen, DROP filter_by_craftsmen, DROP trades, DROP filter_by_trades, DROP maps, DROP filter_by_maps, DROP registration_status, DROP registration_start, DROP registration_end, DROP responded_status, DROP responded_start, DROP responded_end, DROP reviewed_status, DROP reviewed_start, DROP reviewed_end, DROP limit_start, DROP limit_end');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter ADD public_access_identifier LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD was_added_with_client TINYINT(1) DEFAULT NULL, ADD issues LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD any_status INT DEFAULT NULL, ADD filter_by_issues TINYINT(1) DEFAULT \'0\' NOT NULL, ADD craftsmen LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD filter_by_craftsmen TINYINT(1) DEFAULT \'0\' NOT NULL, ADD trades LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD filter_by_trades TINYINT(1) DEFAULT \'0\' NOT NULL, ADD maps LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD filter_by_maps TINYINT(1) DEFAULT \'0\' NOT NULL, ADD registration_status TINYINT(1) DEFAULT NULL, ADD registration_start DATETIME DEFAULT NULL, ADD registration_end DATETIME DEFAULT NULL, ADD responded_status TINYINT(1) DEFAULT NULL, ADD responded_start DATETIME DEFAULT NULL, ADD responded_end DATETIME DEFAULT NULL, ADD reviewed_status TINYINT(1) DEFAULT NULL, ADD reviewed_start DATETIME DEFAULT NULL, ADD reviewed_end DATETIME DEFAULT NULL, ADD limit_start DATETIME DEFAULT NULL, ADD limit_end DATETIME DEFAULT NULL');
    }
}
