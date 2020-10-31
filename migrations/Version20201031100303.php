<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201031100303 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233E83BA6D1B');
        $this->addSql('DROP INDEX IDX_12AD233E83BA6D1B ON issue');
        $this->addSql('ALTER TABLE issue DROP uploaded_at, CHANGE number number INT NOT NULL, CHANGE upload_by_id created_by_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233EB03A8386 FOREIGN KEY (created_by_id) REFERENCES construction_manager (id)');
        $this->addSql('CREATE INDEX IDX_12AD233EB03A8386 ON issue (created_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE issue DROP FOREIGN KEY FK_12AD233EB03A8386');
        $this->addSql('DROP INDEX IDX_12AD233EB03A8386 ON issue');
        $this->addSql('ALTER TABLE issue ADD uploaded_at DATETIME NOT NULL, CHANGE number number INT DEFAULT NULL, CHANGE created_by_id upload_by_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E83BA6D1B FOREIGN KEY (upload_by_id) REFERENCES construction_manager (id)');
        $this->addSql('CREATE INDEX IDX_12AD233E83BA6D1B ON issue (upload_by_id)');
    }
}
