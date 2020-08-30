<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service\Sync\FileServiceResources;

class PublicFileModel
{
    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $displayFilename;

    /**
     * @var string
     */
    public $hash;

    /**
     * @param string $filename
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @param string $displayFilename
     */
    public function setDisplayFilename(string $displayFilename): void
    {
        $this->displayFilename = $displayFilename;
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
    public function getFilename(): string
    {
        return $this->filename;
    }
}
