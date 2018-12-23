<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

class UploadFileCheckResult
{
    /**
     * @var string[]
     */
    private $sameHashConflicts;

    /**
     * @var string|null
     */
    private $fileNameConflict;

    /**
     * @var string
     */
    private $derivedFileName;

    /**
     * @return string[]
     */
    public function getSameHashConflicts(): array
    {
        return $this->sameHashConflicts;
    }

    /**
     * @param string[] $sameHashConflicts
     */
    public function setSameHashConflicts(array $sameHashConflicts): void
    {
        $this->sameHashConflicts = $sameHashConflicts;
    }

    /**
     * @return string|null
     */
    public function getFileNameConflict(): ?string
    {
        return $this->fileNameConflict;
    }

    /**
     * @param string|null $fileNameConflict
     */
    public function setFileNameConflict(?string $fileNameConflict): void
    {
        $this->fileNameConflict = $fileNameConflict;
    }

    /**
     * @return string
     */
    public function getDerivedFileName(): string
    {
        return $this->derivedFileName;
    }

    /**
     * @param string $derivedFileName
     */
    public function setDerivedFileName(string $derivedFileName): void
    {
        $this->derivedFileName = $derivedFileName;
    }
}
