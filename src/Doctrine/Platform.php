<?php

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\Keywords\MariaDb102Keywords;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Types;

class Platform extends MySqlPlatform
{
    public function getTruncateTableSQL($tableName, $cascade = false): string
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
