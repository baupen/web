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

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function setDisplayFilename(string $displayFilename): void
    {
        $this->displayFilename = $displayFilename;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
