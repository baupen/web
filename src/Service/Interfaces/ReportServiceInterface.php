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

use App\Service\Report\ReportElements;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface ReportServiceInterface
{
    /**
     * @param string[] $filters
     */
    public function generatePdfReport(Paginator $issuesPaginator, array $filters, ReportElements $reportElements, ?string $author = null): string;
}
