<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201113154232 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site DROP INDEX IDX_BF4E61B43DA5256D, ADD UNIQUE INDEX UNIQ_BF4E61B43DA5256D (image_id)');
        $this->addSql('ALTER TABLE construction_site CHANGE street_address street_address LONGTEXT NOT NULL, CHANGE postal_code postal_code INT NOT NULL, CHANGE locality locality LONGTEXT NOT NULL, CHANGE country country LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE construction_site_image DROP FOREIGN KEY FK_DFC5717E4994A532');
        $this->addSql('DROP INDEX IDX_DFC5717E4994A532 ON construction_site_image');
        $this->addSql('ALTER TABLE construction_site_image DROP construction_site_id');
        $this->addSql('ALTER TABLE craftsman DROP street_address, DROP postal_code, DROP locality, DROP country');
        $this->addSql('ALTER TABLE filter ADD registered_at_after DATETIME DEFAULT NULL, ADD registered_at_before DATETIME DEFAULT NULL, ADD resolved_at_after DATETIME DEFAULT NULL, ADD resolved_at_before DATETIME DEFAULT NULL, ADD closed_at_after DATETIME DEFAULT NULL, ADD closed_at_before DATETIME DEFAULT NULL, ADD deadline_at_before DATETIME DEFAULT NULL, ADD deadline_at_after DATETIME DEFAULT NULL, ADD craftsman_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ADD craftsman_trades LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ADD map_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', DROP public_access_identifier, DROP was_added_with_client, DROP issues, DROP filter_by_issues, DROP craftsmen, DROP filter_by_craftsmen, DROP trades, DROP filter_by_trades, DROP maps, DROP filter_by_maps, DROP registration_status, DROP registration_start, DROP registration_end, DROP responded_status, DROP responded_start, DROP responded_end, DROP reviewed_status, DROP reviewed_start, DROP reviewed_end, DROP limit_start, DROP limit_end, CHANGE any_status state INT DEFAULT NULL');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E254D80EC');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E3DA5256D');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E83BA6D1B');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EB9690C1F');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EBA91FB54');
        $this->addSql('DROP INDEX IDX_12AD233EBA91FB54 ON issue');
        $this->addSql('DROP INDEX IDX_12AD233EB9690C1F ON issue');
        $this->addSql('DROP INDEX IDX_12AD233E83BA6D1B ON issue');
        $this->addSql('DROP INDEX IDX_12AD233E3DA5256D ON issue');
        $this->addSql('DROP INDEX IDX_12AD233E254D80EC ON issue');
        $this->addSql('ALTER TABLE issue ADD created_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD registered_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD resolved_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD closed_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD deadline DATETIME DEFAULT NULL, ADD resolved_at DATETIME DEFAULT NULL, ADD closed_at DATETIME DEFAULT NULL, DROP upload_by_id, DROP registration_by_id, DROP response_by_id, DROP review_by_id, DROP image_id, DROP response_limit, DROP uploaded_at, DROP responded_at, DROP reviewed_at, CHANGE number number INT NOT NULL');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EB03A8386 FOREIGN KEY (created_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E27E92E18 FOREIGN KEY (registered_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E6713A32B FOREIGN KEY (resolved_by_id) REFERENCES craftsman (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EE1FA7797 FOREIGN KEY (closed_by_id) REFERENCES construction_manager (id)');
        $this->addSql('CREATE INDEX IDX_12AD233EB03A8386 ON issue (created_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E27E92E18 ON issue (registered_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E6713A32B ON issue (resolved_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EE1FA7797 ON issue (closed_by_id)');
        $this->addSql('ALTER TABLE issue_image DROP INDEX IDX_57B76D0C5E7AA58C, ADD UNIQUE INDEX UNIQ_57B76D0C5E7AA58C (issue_id)');
        $this->addSql('ALTER TABLE map_file DROP FOREIGN KEY FK_1FB7918553C55F64');
        $this->addSql('DROP INDEX IDX_1FB7918553C55F64 ON map_file');
        $this->addSql('ALTER TABLE map_file DROP map_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site DROP INDEX UNIQ_BF4E61B43DA5256D, ADD INDEX IDX_BF4E61B43DA5256D (image_id)');
        $this->addSql('ALTER TABLE construction_site CHANGE street_address street_address LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE postal_code postal_code INT DEFAULT NULL, CHANGE locality locality LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE country country LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE construction_site_image ADD construction_site_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE construction_site_image ADD CONSTRAINT FK_DFC5717E4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('CREATE INDEX IDX_DFC5717E4994A532 ON construction_site_image (construction_site_id)');
        $this->addSql('ALTER TABLE craftsman ADD street_address LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD postal_code INT DEFAULT NULL, ADD locality LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD country LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE filter ADD public_access_identifier LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD was_added_with_client TINYINT(1) DEFAULT NULL, ADD issues LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD filter_by_issues TINYINT(1) DEFAULT \'0\' NOT NULL, ADD craftsmen LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD filter_by_craftsmen TINYINT(1) DEFAULT \'0\' NOT NULL, ADD trades LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD filter_by_trades TINYINT(1) DEFAULT \'0\' NOT NULL, ADD maps LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\', ADD filter_by_maps TINYINT(1) DEFAULT \'0\' NOT NULL, ADD registration_status TINYINT(1) DEFAULT NULL, ADD registration_start DATETIME DEFAULT NULL, ADD registration_end DATETIME DEFAULT NULL, ADD responded_status TINYINT(1) DEFAULT NULL, ADD responded_start DATETIME DEFAULT NULL, ADD responded_end DATETIME DEFAULT NULL, ADD reviewed_status TINYINT(1) DEFAULT NULL, ADD reviewed_start DATETIME DEFAULT NULL, ADD reviewed_end DATETIME DEFAULT NULL, ADD limit_start DATETIME DEFAULT NULL, ADD limit_end DATETIME DEFAULT NULL, DROP registered_at_after, DROP registered_at_before, DROP resolved_at_after, DROP resolved_at_before, DROP closed_at_after, DROP closed_at_before, DROP deadline_at_before, DROP deadline_at_after, DROP craftsman_ids, DROP craftsman_trades, DROP map_ids, CHANGE state any_status INT DEFAULT NULL');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EB03A8386');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E27E92E18');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E6713A32B');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EE1FA7797');
        $this->addSql('DROP INDEX IDX_12AD233EB03A8386 ON issue');
        $this->addSql('DROP INDEX IDX_12AD233E27E92E18 ON issue');
        $this->addSql('DROP INDEX IDX_12AD233E6713A32B ON issue');
        $this->addSql('DROP INDEX IDX_12AD233EE1FA7797 ON issue');
        $this->addSql('ALTER TABLE issue ADD upload_by_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD registration_by_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD response_by_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD review_by_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD image_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ADD response_limit DATETIME DEFAULT NULL, ADD uploaded_at DATETIME NOT NULL, ADD responded_at DATETIME DEFAULT NULL, ADD reviewed_at DATETIME DEFAULT NULL, DROP created_by_id, DROP registered_by_id, DROP resolved_by_id, DROP closed_by_id, DROP deadline, DROP resolved_at, DROP closed_at, CHANGE number number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E254D80EC FOREIGN KEY (registration_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E3DA5256D FOREIGN KEY (image_id) REFERENCES issue_image (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E83BA6D1B FOREIGN KEY (upload_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EB9690C1F FOREIGN KEY (review_by_id) REFERENCES construction_manager (id)');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EBA91FB54 FOREIGN KEY (response_by_id) REFERENCES craftsman (id)');
        $this->addSql('CREATE INDEX IDX_12AD233EBA91FB54 ON issue (response_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233EB9690C1F ON issue (review_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E83BA6D1B ON issue (upload_by_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E3DA5256D ON issue (image_id)');
        $this->addSql('CREATE INDEX IDX_12AD233E254D80EC ON issue (registration_by_id)');
        $this->addSql('ALTER TABLE issue_image DROP INDEX UNIQ_57B76D0C5E7AA58C, ADD INDEX IDX_57B76D0C5E7AA58C (issue_id)');
        $this->addSql('ALTER TABLE map_file ADD map_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE map_file ADD CONSTRAINT FK_1FB7918553C55F64 FOREIGN KEY (map_id) REFERENCES map (id)');
        $this->addSql('CREATE INDEX IDX_1FB7918553C55F64 ON map_file (map_id)');
    }
}
