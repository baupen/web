<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf;

class PdfDefinition
{
    private string $title;

    private string $author;

    private string $logoPath;

    /**
     * PdfDefinition constructor.
     */
    public function __construct(string $title, string $author, string $logoPath)
    {
        $this->title = $title;
        $this->author = $author;
        $this->logoPath = $logoPath;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getLogoPath(): string
    {
        return $this->logoPath;
    }
}
