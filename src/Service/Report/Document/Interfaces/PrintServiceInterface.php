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
     */
    public function printParagraph(string $paragraph);

    /**
     * @param string $header
     */
    public function printRegionHeader(string $header);

    /**
     * @param string[] $header
     * @param string[][] $content
     */
    public function printTable(array $header, array $content);

    /**
     * @param string[] $header
     * @param string[][] $content
     */
    public function printImage(array $header, array $content);

    /**
     * ends the active region & adds appropriate spacing.
     */
    public function endRegion();

    /**
     * will avoid a page break between the next printed elements
     * will add a page break before all elements if they do not fit on the same page
     * active until end region is called.
     */
    public function startGroupedRegion();

    /**
     * starts a region with columns.
     *
     * @param int $columnCount
     */
    public function startColumnedRegion(int $columnCount);

    /**
     * ensures the next printed elements are printed in the specified column
     * will throw an exception if the column region does not exist.
     *
     * @param int $column
     */
    public function goToColumn(int $column);
}
