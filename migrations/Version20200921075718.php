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
final class Version20200921075718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_manager DROP FOREIGN KEY FK_450D96CDA7F12217');
        $this->addSql('DROP INDEX IDX_450D96CDA7F12217 ON construction_manager');
        $this->addSql('ALTER TABLE construction_manager DROP active_construction_site_id');
        $this->addSql('ALTER TABLE email ADD identifier CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_manager ADD active_construction_site_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE construction_manager ADD CONSTRAINT FK_450D96CDA7F12217 FOREIGN KEY (active_construction_site_id) REFERENCES construction_site (id)');
        $this->addSql('CREATE INDEX IDX_450D96CDA7F12217 ON construction_manager (active_construction_site_id)');
        $this->addSql('ALTER TABLE email DROP identifier');
    }
}
