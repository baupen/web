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
    use TimeTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $hash;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $versionId;

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
    public function getVersionId(): string
    {
        return $this->versionId;
    }

    /**
     * @param string $versionId
     */
    public function setVersionId(string $versionId): void
    {
        $this->versionId = $versionId;
    }
}
