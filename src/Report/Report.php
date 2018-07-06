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

use App\Entity\ConstructionSite;
use App\Entity\Filter;
use App\Entity\Issue;
use App\Entity\Map;

class Report
{
    /**
     * @var Pdf
     */
    private $pdfDocument;

    /**
     * @var PdfSizes
     */
    private $pdfSizes;

    /**
     * @var string
     */
    private $publicPath = __DIR__ . '/../../public';

    /**
     * @var float|null
     */
    private $currentContentHeight = null;

    public function __construct(PdfDefinition $pdfDefinition)
    {
        $this->pdfSizes = new PdfSizes();
        $this->pdfDocument = new Pdf($pdfDefinition, $this->pdfSizes);

        // default fill & font color
        $this->pdfDocument->SetFillColor(200, 200, 200);
        $this->pdfDocument->SetTextColor(33, 37, 41);
    }

    /**
     * @return float|int
     */
    private function getCurrentY()
    {
        if ($this->currentContentHeight === null) {
            $this->pdfDocument->AddPage();
            $this->currentContentHeight = 0;
        }

        return $this->pdfSizes->getContentYStart() + $this->currentContentHeight;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param string[] $filterEntries
     */
    public function addIntroduction(ConstructionSite $constructionSite, $filterEntries)
    {
        $maxContentHeight = $this->getCurrentY();

        //three or two column layout
        $columnCount = 3;
        $currentColumn = 0;

        //image
        $imagePath = $this->publicPath . '/' . $constructionSite->getImageFilePath();
        if (file_exists($imagePath)) {
            $maxImageWidth = $this->pdfSizes->getColumnSize($columnCount);
            list($width, $height) = $this->pdfSizes->getWidthHeightArguments($imagePath, $maxImageWidth, $maxImageWidth);
            $this->pdfDocument->Image($imagePath, $this->pdfSizes->getContentXStart(), $this->getCurrentY(), $width, $height);
            $maxContentHeight = max($height, $maxContentHeight);

            //set position for the next content
            ++$currentColumn;
        } else {
            $columnCount = 2;
        }

        $columnWidth = $this->pdfSizes->getColumnSize($columnCount);

        //construction site description
        $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $this->getCurrentY());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->Cell($columnWidth, 0, $constructionSite->getName(), 0, 1);
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->Cell($columnWidth, 0, implode(', ', $constructionSite->getAddressLines()), 0, 2);
        $maxContentHeight = max($this->pdfDocument->GetY(), $maxContentHeight);
        ++$currentColumn;

        //filter used for generation
        $this->currentContentHeight = $maxContentHeight;
        $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $this->getCurrentY());
        foreach ($filterEntries as $name => $value) {
            $this->pdfDocument->SetFont($this->pdfSizes->getRegularFontSize(), 'b');
            $this->pdfDocument->Cell(0, 0, $name . ': ');
            $this->pdfDocument->SetFont($this->pdfSizes->getRegularFontSize());
            $this->pdfDocument->Cell(0, 0, $value);
        }

        //save content height for next part
        $this->currentContentHeight = max($this->pdfDocument->GetY(), $maxContentHeight);
    }

    /**
     * @param string $targetFilePath
     */
    public function save($targetFilePath)
    {
        $this->pdfDocument->Output($targetFilePath, 'F');
    }

    /**
     * @param Map $map
     * @param Issue[] $issues
     * @param string $mapImageFilePath
     */
    public function addMap($map, $mapImageFilePath, array $issues)
    {
    }

    public function addTable($tableHeader, $tableContent)
    {
        $columnCount = count($tableHeader);
        $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $this->getCurrentY());
        //$this->pdfDocument->Cell($columnWidth, )
    }
}
