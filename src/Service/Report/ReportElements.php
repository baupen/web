<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report;

class ReportElements
{
    /**
     * @var bool
     */
    private $tableByCraftsman = false;

    /**
     * @var bool
     */
    private $tableByMap = true;

    /**
     * @var bool
     */
    private $withImages = true;

    public static function fromRequest(array $parameters): self
    {
        $self = new self();

        $self->tableByCraftsman = static::getValue($parameters, 'tableByCraftsman', false);
        $self->tableByMap = static::getValue($parameters, 'tableByMap', true);
        $self->withImages = static::getValue($parameters, 'withImages', true);

        return $self;
    }

    private static function getValue(array $parameters, string $config, bool $default): bool
    {
        $key = 'report['.$config.']';
        if (!isset($parameters[$key])) {
            return $default;
        }

        return (bool) $parameters[$key];
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
}
