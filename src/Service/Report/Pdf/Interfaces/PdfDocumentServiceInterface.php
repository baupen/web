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

interface PdfDocumentServiceInterface
{
    /**
     * @param string $header
     * @param string $footer
     * @param string $logoPath
     *
     * @return PdfDocumentInterface
     */
    public function create(string $header, string $footer, string $logoPath);
}
