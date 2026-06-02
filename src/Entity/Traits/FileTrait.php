<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/*
 * saves infos about a file
 */
trait FileTrait
{
    #[ORM\Column(type: Types::TEXT)]
    private ?string $filename = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $hash = null;

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }
}
