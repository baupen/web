<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 6/19/18
 * Time: 10:08 AM
 */

namespace App\Api\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ObjectMeta
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $lastChangeTime;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLastChangeTime(): string
    {
        return $this->lastChangeTime;
    }

    /**
     * @param string $lastChangeTime
     */
    public function setLastChangeTime(string $lastChangeTime): void
    {
        $this->lastChangeTime = $lastChangeTime;
    }
}
