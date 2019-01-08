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

use App\Service\Report\Pdf\Interfaces\PrintableLayoutInterface;

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
     * will call the closure with the printer as the first and the default width as the second argument.
     *
     * @param \Closure $param
     */
    public function printCustom(\Closure $param);

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
}
