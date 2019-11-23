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

interface PrinterInterface
{
    public function printTitle(string $title);

    public function printParagraph(string $paragraph);

    /**
     * @param string[] $keyValues
     */
    public function printKeyValueParagraph(array $keyValues);

    public function printRegionHeader(string $header);

    public function printImage(string $filePath);

    public function printIssueImage(string $imagePath, int $number);
}
