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
    private $tableByTrade = false;

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

    public static function fromRequest(array $parameters)
    {
        $self = new self();

        $self->tableByTrade = static::getValue($parameters, 'table_by_trade', false);
        $self->tableByTrade = static::getValue($parameters, 'table_by_craftsman', false);
        $self->tableByTrade = static::getValue($parameters, 'table_by_map', true);
        $self->tableByTrade = static::getValue($parameters, 'with_images', true);

        return $self;
    }

    private static function getValue(array $parameters, string $config, bool $default)
    {
        $key = 'report['.$config.']';
        if (!isset($parameters[$key])) {
            return $default;
        }

        return (bool) $parameters[$key];
    }

    /**
     * @return static
     */
    public static function forCraftsman()
    {
        $elem = new static();

        return $elem;
    }

    public function getTableByTrade(): bool
    {
        return $this->tableByTrade;
    }

    public function setTableByTrade(bool $tableByTrade): void
    {
        $this->tableByTrade = $tableByTrade;
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
