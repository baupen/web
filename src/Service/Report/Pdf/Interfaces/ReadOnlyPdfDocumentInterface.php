<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Interfaces;

interface ReadOnlyPdfDocumentInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * returns the active cursor position as an array of [$xCoordinate, $yCoordinate].
     *
     * @return int[]
     */
    public function getCursor();

    /**
     * @return int
     */
    public function getPage();
}
