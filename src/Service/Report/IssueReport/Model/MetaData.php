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

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $header
     */
    public function setTitle(string $header): void
    {
        $this->title = $header;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getGenerationInfoText(): string
    {
        return $this->generationInfoText;
    }

    /**
     * @param string $footer
     */
    public function setGenerationInfoText(string $footer): void
    {
        $this->generationInfoText = $footer;
    }

    /**
     * @param string $logoPath
     */
    public function setLogoPath(string $logoPath): void
    {
        $this->logoPath = $logoPath;
    }

    /**
     * @return string
     */
    public function getLogoPath(): string
    {
        return $this->logoPath;
    }
}
