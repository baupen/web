<?php

namespace App\Service\Upload;

class UploadFileCheck
{
    private bool $uploadPossible;

    /**
     * @var string[]
     */
    private ?array $sameHashConflicts = null;

    private ?string $fileNameConflict = null;

    private ?string $derivedFileName = null;

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

    public function getFileNameConflict(): ?string
    {
        return $this->fileNameConflict;
    }

    public function setFileNameConflict(?string $fileNameConflict): void
    {
        $this->fileNameConflict = $fileNameConflict;
    }

    public function getDerivedFileName(): ?string
    {
        return $this->derivedFileName;
    }

    public function setDerivedFileName(?string $derivedFileName): void
    {
        $this->derivedFileName = $derivedFileName;
    }

    public function isUploadPossible(): bool
    {
        return $this->uploadPossible;
    }

    public function setUploadPossible(bool $uploadPossible): void
    {
        $this->uploadPossible = $uploadPossible;
    }
}
