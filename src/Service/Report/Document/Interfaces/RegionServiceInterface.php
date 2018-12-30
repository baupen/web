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

interface RegionServiceInterface
{
    /**
     * @param DocumentInterface $document
     * @param int $columnCount
     *
     * @return PrintServiceInterface
     */
    public function startRegion(DocumentInterface $document, int $columnCount = 1);

    /**
     * @param DocumentInterface $document
     *
     * @return PrintServiceInterface
     */
    public function startGroup(DocumentInterface $document);
}
