<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report;

use App\Service\Report\Pdf\Interfaces\DocumentInterface;
use App\Service\Report\Pdf\Interfaces\InitializationServiceInterface;

abstract class InitializationService implements InitializationServiceInterface
{
    /**
     * @param string $title
     * @param string $author
     *
     * @return DocumentInterface
     */
    public function createDocument(string $title, string $author)
    {
        // TODO: Implement createDocument() method.
    }

    /**
     * @param DocumentInterface $pdfDocument
     * @param string $savePath
     */
    public function saveDocument(DocumentInterface $pdfDocument, string $savePath)
    {
        // TODO: Implement saveDocument() method.
    }
}
