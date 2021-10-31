<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\Keywords\MariaDb102Keywords;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Types;

class Platform extends MySqlPlatform
{
    /**
     * {@inheritdoc}
     */
    public function getTruncateTableSQL($tableName, $cascade = false)
    {
        $truncateSql = parent::getTruncateTableSQL($tableName, $cascade);

        return 'SET foreign_key_checks = 0;'.$truncateSql.';SET foreign_key_checks = 1;';
    }

    // methods copied out of MariaDb1027Platform
    public function getJsonTypeDeclarationSQL(array $column): string
    {
        return 'LONGTEXT';
    }

    protected function getReservedKeywordsClass(): string
    {
        return MariaDb102Keywords::class;
    }

    protected function initializeDoctrineTypeMappings(): void
    {
        parent::initializeDoctrineTypeMappings();

        $this->doctrineTypeMapping['json'] = Types::JSON;
    }
}
