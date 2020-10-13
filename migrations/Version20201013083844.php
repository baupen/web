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
final class Version20201013083844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site DROP FOREIGN KEY FK_BF4E61B43DA5256D');
        $this->addSql('DROP INDEX IDX_BF4E61B43DA5256D ON construction_site');
        $this->addSql('ALTER TABLE construction_site DROP image_id');
        $this->addSql('ALTER TABLE construction_site_image DROP INDEX IDX_DFC5717E4994A532, ADD UNIQUE INDEX UNIQ_DFC5717E4994A532 (construction_site_id)');
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E3DA5256D');
        $this->addSql('DROP INDEX IDX_12AD233E3DA5256D ON issue');
        $this->addSql('ALTER TABLE issue DROP image_id');
        $this->addSql('ALTER TABLE issue_image DROP INDEX IDX_57B76D0C5E7AA58C, ADD UNIQUE INDEX UNIQ_57B76D0C5E7AA58C (issue_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE construction_site ADD image_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE construction_site ADD CONSTRAINT FK_BF4E61B43DA5256D FOREIGN KEY (image_id) REFERENCES construction_site_image (id)');
        $this->addSql('CREATE INDEX IDX_BF4E61B43DA5256D ON construction_site (image_id)');
        $this->addSql('ALTER TABLE construction_site_image DROP INDEX UNIQ_DFC5717E4994A532, ADD INDEX IDX_DFC5717E4994A532 (construction_site_id)');
        $this->addSql('ALTER TABLE issue ADD image_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E3DA5256D FOREIGN KEY (image_id) REFERENCES issue_image (id)');
        $this->addSql('CREATE INDEX IDX_12AD233E3DA5256D ON issue (image_id)');
        $this->addSql('ALTER TABLE issue_image DROP INDEX UNIQ_57B76D0C5E7AA58C, ADD INDEX IDX_57B76D0C5E7AA58C (issue_id)');
    }
}
