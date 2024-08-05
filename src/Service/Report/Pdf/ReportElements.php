<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf;

class ReportElements
{
    private bool $tableByCraftsman = false;

    private bool $tableByMap = true;

    private bool $withImages = true;

    private bool $withRenders = true;

    private bool $groupIssuesByCraftsman = true;

    public static function fromRequest(?array $parameters = null): self
    {
        $self = new self();

        $self->tableByCraftsman = static::getValue($parameters, 'tableByCraftsman', false);
        $self->tableByMap = static::getValue($parameters, 'tableByMap', false);
        $self->withImages = static::getValue($parameters, 'withImages', true);
        $self->withRenders = static::getValue($parameters, 'withRenders', true);
        $self->groupIssuesByCraftsman = static::getValue($parameters, 'groupIssuesByCraftsman', true);

        return $self;
    }

    private static function getValue(?array $parameters, string $key, bool $default): bool
    {
        if (null === $parameters || !isset($parameters[$key])) {
            return $default;
        }

        $value = $parameters[$key];

        return '1' === $value || 'true' === $value;
    }

    public function getTableByCraftsman(): bool
    {
        return $this->tableByCraftsman;
    }

    public function setTableByCraftsman(bool $tableByCraftsman): void
    {
        $this->tableByCraftsman = $tableByCraftsman;
    }

    public function getTableByMap(): bool
    {
        return $this->tableByMap;
    }

    public function setTableByMap(bool $tableByMap): void
    {
        $this->tableByMap = $tableByMap;
    }

    public function getWithImages(): bool
    {
        return $this->withImages;
    }

    public function setWithImages(bool $withImages): void
    {
        $this->withImages = $withImages;
    }

    public function getWithRenders(): bool
    {
        return $this->withRenders;
    }

    public function getGroupIssuesByCraftsman(): bool
    {
        return $this->groupIssuesByCraftsman;
    }

    public function setGroupIssuesByCraftsman(bool $groupIssuesByCraftsman): void
    {
        $this->groupIssuesByCraftsman = $groupIssuesByCraftsman;
    }
}
