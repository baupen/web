<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/*
 * saves infos about a file
 */
trait FileTrait
{
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $displayFilename;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $hash;

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getDisplayFilename(): string
    {
        return $this->displayFilename;
    }

    /**
     * @param string $displayFilename
     */
    public function setDisplayFilename(string $displayFilename): void
    {
        $this->displayFilename = $displayFilename;
    }
}
