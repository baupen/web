<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Email;

class TableWithLink
{
    /**
     * @var string
     */
    private $key = 'TABLE_WITH_LINK';

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string[]
     */
    private $header = [];
    /**
     * @var string[][]
     */
    private $content = [];

    /**
     * @var string[]
     */
    private $footer = [];

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    /**
     * @return string[]
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @param string[] $header
     */
    public function setHeader(array $header): void
    {
        $this->header = $header;
    }

    /**
     * @return \string[][]
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param \string[][] $content
     */
    public function setContent(array $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string[]
     */
    public function getFooter(): array
    {
        return $this->footer;
    }

    /**
     * @param string[] $footer
     */
    public function setFooter(array $footer): void
    {
        $this->footer = $footer;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
