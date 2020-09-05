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
final class Version20200905103600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sqlite = $this->getSqlitePDO();
        if (null === $sqlite) {
            return;
        }

        $this->migrateTrivialTable($sqlite, 'email');
    }

    public function down(Schema $schema): void
    {
        $this->connection->exec('DELETE FROM email');
    }

    private function migrateTrivialTable(\PDO $source, string $name)
    {
        $query = $source->prepare('SELECT * FROM '.$name);
        $query->execute();

        $results = $query->fetchAll();
        if (count($results) > 0) {
            $this->connection->insert('email', $results);
        }
    }

    private function getSqlitePDO(): ?\PDO
    {
        $sqlitePath = __DIR__.'/../var/persistent/data.sqlite';
        if (!file_exists($sqlitePath)) {
            $this->write('no existing sqlite database found, skipping migration.');

            return null;
        }

        return new \PDO('sqlite:'.$sqlitePath);
    }
}
