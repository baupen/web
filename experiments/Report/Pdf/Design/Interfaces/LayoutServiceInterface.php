<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\IssueReport\Pdf\Design\Interfaces;

use PdfGenerator\Pdf\LayoutFactoryConfigurationInterface;

interface LayoutServiceInterface extends LayoutFactoryConfigurationInterface
{
    /**
     * the total width of the document.
     */
    public function getPageSizeY(): float;

    public function getHeaderYStart(): float;

    public function getHeaderHeight(): float;

    public function getContentXStart(): float;

    /**
     * the width of the document till the right margin.
     */
    public function getContentXEnd(): float;

    public function getContentYStart(): float;

    /**
     * the width of the document till the bottom margin.
     */
    public function getContentYEnd(): float;

    /**
     * the width of the content of the document.
     */
    public function getContentYSize(): float;

    public function getFooterYStart(): float;

    public function getMarginBottom(): float;

    public function getMarginRight(): float;

    public function getRegionSpacer(): float;
}
