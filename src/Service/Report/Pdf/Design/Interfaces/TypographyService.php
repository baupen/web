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

use App\Service\Report\Pdf\Interfaces\DocumentInterface;

interface TypographyService
{
    /**
     * @param DocumentInterface $document
     */
    public function ensureParagraph(DocumentInterface $document);

    /**
     * @param DocumentInterface $document
     */
    public function ensureTableHeader(DocumentInterface $document);

    /**
     * @param DocumentInterface $document
     */
    public function ensureTableContent(DocumentInterface $document);
}
