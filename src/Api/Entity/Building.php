<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;


use App\Api\Entity\Base\BaseEntity;

class Building
{
    use BaseEntity;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * @var string|null
     */
    private $imageFilename;

    /**
     * @var string[]
     */
    private $maps;

    /**
     * @var string[]
     */
    private $craftsmen;

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
}