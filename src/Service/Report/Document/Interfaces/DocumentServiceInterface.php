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

interface DocumentServiceInterface
{
    /**
     * @param string $title
     * @param string $author
     *
     * @return DocumentInterface
     */
    public function create(string $title, string $author);

    /**
     * @param DocumentInterface $pdfDocument
     * @param string $savePath
     */
    public function save(DocumentInterface $pdfDocument, string $savePath);
}
