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
final class Version20200906094907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site ADD deleted_at DATETIME DEFAULT NULL, DROP is_automatic_edit_enabled');
        $this->addSql('ALTER TABLE craftsman ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE issue ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE map ADD deleted_at DATETIME DEFAULT NULL, DROP is_automatic_edit_enabled');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site ADD is_automatic_edit_enabled LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP deleted_at');
        $this->addSql('ALTER TABLE craftsman DROP deleted_at');
        $this->addSql('ALTER TABLE issue DROP deleted_at');
        $this->addSql('ALTER TABLE map ADD is_automatic_edit_enabled LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP deleted_at');
    }
}
