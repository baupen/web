<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Report;

class Report
{
    /**
     * @var Pdf
     */
    private $pdfDocument;

    /**
     * @var ReportDefinition
     */
    private $reportDefinition;

    public function __construct(ReportDefinition $reportDefinition)
    {
        $this->reportDefinition = $reportDefinition;
        $this->pdfDocument = new Pdf($this->reportDefinition);
    }

    /**
     * generates the report.
     *
     * @param $targetFilePath
     */
    public function generate($targetFilePath)
    {
        $this->pdfDocument->AddPage();
        $this->pdfDocument->AddPage();
        $this->pdfDocument->AddPage();

        //save to local file
        $this->pdfDocument->Output($targetFilePath, 'F');
    }
}
