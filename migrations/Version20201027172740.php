<?php

declare(strict_types=1);

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201027172740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site ADD image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE construction_site ADD CONSTRAINT FK_BF4E61B43DA5256D FOREIGN KEY (image_id) REFERENCES construction_site_image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BF4E61B43DA5256D ON construction_site (image_id)');
        $this->addSql('ALTER TABLE construction_site_image DROP FOREIGN KEY FK_DFC5717E4994A532');
        $this->addSql('DROP INDEX UNIQ_DFC5717E4994A532 ON construction_site_image');
        $this->addSql('ALTER TABLE construction_site_image DROP construction_site_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site DROP FOREIGN KEY FK_BF4E61B43DA5256D');
        $this->addSql('DROP INDEX UNIQ_BF4E61B43DA5256D ON construction_site');
        $this->addSql('ALTER TABLE construction_site DROP image_id');
        $this->addSql('ALTER TABLE construction_site_image ADD construction_site_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE construction_site_image ADD CONSTRAINT FK_DFC5717E4994A532 FOREIGN KEY (construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DFC5717E4994A532 ON construction_site_image (construction_site_id)');
    }
}
