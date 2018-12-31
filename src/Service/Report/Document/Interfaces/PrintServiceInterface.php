<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Document\Interfaces;

interface PrintServiceInterface
{
    /**
     * @param string $paragraph
     *
     * @return DocumentInterface
     */
    public function printParagraph(string $paragraph);

    /**
     * @param string $header
     *
     * @return DocumentInterface
     */
    public function printRegionHeader(string $header);

    /**
     * @param string[] $header
     * @param string[][] $content
     *
     * @return DocumentInterface
     */
    public function printTable(array $header, array $content);

    /**
     * @param string[] $header
     * @param string[][] $content
     *
     * @return DocumentInterface
     */
    public function printImage(array $header, array $content);
}
