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

    /**
     * @return static
     */
    public static function forCraftsman()
    {
        $elem = new static();

        return $elem;
    }

    /**
     * @return bool
     */
    public function getTableByTrade(): bool
    {
        return $this->tableByTrade;
    }

    /**
     * @param bool $tableByTrade
     */
    public function setTableByTrade(bool $tableByTrade): void
    {
        $this->tableByTrade = $tableByTrade;
    }

    /**
     * @return bool
     */
    public function getTableByCraftsman(): bool
    {
        return $this->tableByCraftsman;
    }

    /**
     * @param bool $tableByCraftsman
     */
    public function setTableByCraftsman(bool $tableByCraftsman): void
    {
        $this->tableByCraftsman = $tableByCraftsman;
    }

    /**
     * @return bool
     */
    public function getTableByMap(): bool
    {
        return $this->tableByMap;
    }

    /**
     * @param bool $tableByMap
     */
    public function setTableByMap(bool $tableByMap): void
    {
        $this->tableByMap = $tableByMap;
    }

    /**
     * @return bool
     */
    public function getWithImages(): bool
    {
        return $this->withImages;
    }

    /**
     * @param bool $withImages
     */
    public function setWithImages(bool $withImages): void
    {
        $this->withImages = $withImages;
    }
}
