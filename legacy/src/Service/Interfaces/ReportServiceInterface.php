<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Service\Report\ReportElements;

interface ReportServiceInterface
{
    /**
     * @param string $author
     *
     * @return string
     */
    public function generatePdfReport(ConstructionSite $constructionSite, Filter $filter, ?string $author, ReportElements $reportElements);
}
