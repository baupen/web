<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/*
 * the id used in the entities
 */

trait IdTrait
{
    /**
     * @var string|null
     *                  will be null when not inserted into the db yet
     */
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: \Doctrine\DBAL\Types\Types::GUID)]
    #[ORM\GeneratedValue(strategy: 'UUID')]
    private $id;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
