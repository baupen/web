<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Design\Interfaces;

interface LayoutServiceInterface
{
    /**
     * the total width of the document.
     *
     * @return float
     */
    public function getPageSizeY(): float;

    /**
     * @return float
     */
    public function getHeaderYStart(): float;

    /**
     * @return float
     */
    public function getHeaderHeight(): float;

    /**
     * @return float
     */
    public function getContentXStart(): float;

    /**
     * the width of the document till the right margin.
     *
     * @return float
     */
    public function getContentXEnd(): float;

    /**
     * @return float
     */
    public function getContentXSize(): float;

    /**
     * @return float
     */
    public function getContentYStart(): float;

    /**
     * the width of the document till the bottom margin.
     *
     * @return float
     */
    public function getContentYEnd(): float;

    /**
     * the width of the content of the document.
     *
     * @return float
     */
    public function getContentYSize(): float;

    /**
     * @return float
     */
    public function getFooterYStart(): float;

    /**
     * @return float
     */
    public function getMarginBottom(): float;
}
