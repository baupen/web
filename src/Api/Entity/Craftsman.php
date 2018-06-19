<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


use App\Api\Entity\Base\BaseEntity;

class Craftsman
{
    use BaseEntity;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $trade;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getTrade(): string
    {
        return $this->trade;
    }

    /**
     * @param string $trade
     */
    public function setTrade(string $trade): void
    {
        $this->trade = $trade;
    }
}