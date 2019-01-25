<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Interfaces;

use App\Service\Report\Document\Interfaces\Layout\Base\PrintableLayoutInterface;
use App\Service\Report\Pdf\Cursor;

interface BuildingBlocksInterface
{
    /**
     * @param string $title
     */
    public function printTitle(string $title);

    /**
     * @param string $paragraph
     */
    public function printParagraph(string $paragraph);

    /**
     * @param string[] $keyValues
     */
    public function printKeyValueParagraph(array $keyValues);

    /**
     * @param string $header
     */
    public function printRegionHeader(string $header);

    /**
     * @param string $filePath
     */
    public function printImage(string $filePath);

    /**
     * @param PrintableLayoutInterface $layout
     */
    public function setLayout(PrintableLayoutInterface $layout);

    /**
     * @param string $imagePath
     * @param int $number
     */
    public function printIssueImage(string $imagePath, int $number);

    /**
     * @param Cursor $start
     * @param Cursor $end
     */
    public function drawTableAlternatingBackground(Cursor $start, Cursor $end);
}
