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
        return 'migrate data from sqlite file if exist';
    }

    public function up(Schema $schema): void
    {
        $sqlite = $this->getSqlitePDO();
        if (null === $sqlite) {
            return;
        }

        $this->migrate($sqlite, 'construction_manager');
        $this->migrate($sqlite, 'construction_site');
        $this->migrateFile($sqlite, 'construction_site_image');
        $this->migrateMap($sqlite);
        $this->migrateFile($sqlite, 'map_file');
        $this->migrate($sqlite, 'craftsman');
        $this->migrateIssue($sqlite);
        $this->migrateFile($sqlite, 'issue_image');

        $this->migrate($sqlite, 'construction_site_construction_manager');
        $this->migrate($sqlite, 'authentication_token');
        $this->migrateEmail($sqlite);
        $this->migrate($sqlite, 'filter');
    }

    private function migrateEmail(\PDO $source): void
    {
        $this->migrate($source, 'email', function (&$email) {
            if ('system' === $email['sender_name']) {
                unset($email['sender_name']);
                unset($email['sender_email']);
            }
        });
    }

    private function migrateFile(\PDO $source, string $tableName): void
    {
        $this->migrate($source, $tableName, function (&$map) {
            unset($map['displayFilename']);
        });
    }

    private function migrateMap(\PDO $source): void
    {
        $this->migrate($source, 'map', function (&$map) {
            unset($map['sector_frame']);
        });
    }

    private function migrateIssue(\PDO $source): void
    {
        $issuePositionByIssueId = $this->getLookupFromDatabase($source, 'issue_position', 'issue_id');
        $mapByIssueId = $this->getLookupFromDatabase($source, 'map', 'id');

        $this->migrate($source, 'issue', function (&$issue) use ($issuePositionByIssueId, $mapByIssueId) {
            // include issue position table
            if (key_exists($issue['id'], $issuePositionByIssueId)) {
                $issuePosition = $issuePositionByIssueId[$issue['id']];
                $issue['position_x'] = $issuePosition['position_x'];
                $issue['position_y'] = $issuePosition['position_y'];
                $issue['position_zoom_scale'] = $issuePosition['position_zoom_scale'];
                $issue['map_file_id'] = $issuePosition['map_file_id'];
            } else {
                assert(null === $issue['position']);
            }
            unset($issue['position']);

            // add construction_site_id property
            $issue['construction_site_id'] = $mapByIssueId[$issue['map_id']]['construction_site_id'];
        });
    }

    private function getLookupFromDatabase(\PDO $source, string $tableName, string $lookupPropertyName): array
    {
        $issuePositions = $this->getFullDatabase($source, $tableName);
        $issuePositionByIssueId = [];
        foreach ($issuePositions as $issuePosition) {
            $issuePositionByIssueId[$issuePosition[$lookupPropertyName]] = $issuePosition;
        }

        return $issuePositionByIssueId;
    }

    public function down(Schema $schema): void
    {
        $this->connection->exec('DELETE FROM email');
        $this->connection->exec('DELETE FROM authentication_token');
        $this->connection->exec('DELETE FROM filter');
        $this->connection->exec('DELETE FROM construction_site_construction_manager');

        $this->connection->exec('DELETE FROM issue_image');
        $this->connection->exec('DELETE FROM issue');
        $this->connection->exec('DELETE FROM craftsman');
        $this->connection->exec('DELETE FROM map_file');
        $this->connection->exec('DELETE FROM map');
        $this->connection->exec('DELETE FROM construction_site_image');
        $this->connection->exec('DELETE FROM construction_site');
        $this->connection->exec('DELETE FROM construction_site_manager');
    }

    private function migrate(\PDO $source, string $name, ?callable $conversion = null): void
    {
        $content = $this->getFullDatabase($source, $name);
        if (0 === count($content)) {
            return;
        }

        foreach ($content as &$item) {
            if (null !== $conversion) {
                $conversion($item);
            }
        }

        $this->connection->insert('email', $content);
    }

    private function getFullDatabase(\PDO $source, string $name): array
    {
        $query = $source->prepare('SELECT * FROM '.$name);
        $query->execute();

        return $query->fetchAll();
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
