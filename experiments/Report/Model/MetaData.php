<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Model;

class MetaData
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * @var string
     */
    private $generationInfoText;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $header): void
    {
        $this->title = $header;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author)
    {
        $this->author = $author;
    }

    public function getGenerationInfoText(): string
    {
        return $this->generationInfoText;
    }

    public function setGenerationInfoText(string $footer): void
    {
        $this->generationInfoText = $footer;
    }

    public function setLogoPath(string $logoPath): void
    {
        $this->logoPath = $logoPath;
    }

    public function getLogoPath(): string
    {
        return $this->logoPath;
    }
}
