<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Interfaces\PdfDocument;

use App\Service\Report\Pdf\Cursor;
use App\Service\Report\Pdf\Tcpdf\Configuration\PrintConfiguration;

interface PdfDocumentStateInterface
{
    /**
     * returns the active cursor position as an array of [$xCoordinate, $yCoordinate, $page].
     *
     * @return Cursor
     */
    public function getCursor();

    /**
     * @param Cursor $cursor
     */
    public function setCursor(Cursor $cursor);

    /**
     * @return PrintConfiguration
     */
    public function getConfiguration();

    /**
     * @param PrintConfiguration $printConfiguration
     */
    public function setConfiguration(PrintConfiguration $printConfiguration);

    /**
     * @param array $config
     * @param bool $restoreDefaults
     */
    public function configure(array $config = [], bool $restoreDefaults = true);
}
