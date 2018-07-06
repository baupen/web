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
use App\Helper\ImageHelper;

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
     * @var PdfDesign
     */
    private $pdfDesign;

    /**
     * @var string
     */
    private $publicPath = __DIR__ . '/../../public';

    /**
     * @var float|null
     */
    private $currentHeight = null;

    public function __construct(PdfDefinition $pdfDefinition)
    {
        $this->pdfSizes = new PdfSizes();
        $this->pdfDesign = new PdfDesign();
        $this->pdfDocument = new Pdf($pdfDefinition, $this->pdfSizes);

        //prepare fonts
        $checkFilePath = K_PATH_FONTS . '/.copied';
        if (!file_exists($checkFilePath)) {
            //copy all fonts from the assets to the fonts folder of tcpdf
            shell_exec('\cp -r ' . K_PATH_FONTS . '/* ' . K_PATH_FONTS);
            file_put_contents($checkFilePath, time());
        }

        $this->setDefaults();
    }

    private function setDefaults()
    {
        //set typography
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());

        // set colors
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLightBackground());
        $this->pdfDocument->SetDrawColor(...$this->pdfDesign->getDarkBackground());
        $this->pdfDocument->SetTextColor(...$this->pdfDesign->getTextColor());

        //set layout
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getContentXStart());
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getDefaultCellPadding());
    }

    /**
     * @return float|int
     */
    private function getCurrentY()
    {
        if ($this->currentHeight === null) {
            $this->pdfDocument->AddPage();
            $this->currentHeight = $this->pdfSizes->getContentYStart();
        }

        return $this->currentHeight;
    }

    /**
     * @param $currentY
     */
    private function setCurrentY($currentY)
    {
        $this->currentHeight = $currentY;
    }

    /**
     * @param ConstructionSite $constructionSite
     * @param string[] $filterEntries
     * @param string $filterHeader
     */
    public function addIntroduction(ConstructionSite $constructionSite, array $filterEntries, string $filterHeader)
    {
        $maxContentHeight = $this->getCurrentY();

        //three or two column layout
        $columnCount = 3;
        $currentColumn = 0;

        //image
        $imagePath = $this->publicPath . '/' . $constructionSite->getImageFilePath();
        if (file_exists($imagePath)) {
            $maxImageWidth = $this->pdfSizes->getColumnContentWidth($columnCount);
            list($width, $height) = ImageHelper::getWidthHeightArguments($imagePath, $maxImageWidth, $maxImageWidth);
            $this->pdfDocument->Image($imagePath, $this->pdfSizes->getContentXStart(), $this->getCurrentY(), $width, $height);
            $maxContentHeight = max($this->pdfDocument->GetY() + $height, $maxContentHeight);

            //set position for the next content
            ++$currentColumn;
        } else {
            --$columnCount;
        }

        $columnWidth = $this->pdfSizes->getColumnContentWidth($columnCount);

        //construction site description
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
        $this->pdfDocument->SetY($this->getCurrentY());

        $this->printH3($constructionSite->getName(), $columnWidth);

        $this->printP(implode("\n", $constructionSite->getAddressLines()), $columnWidth);
        $maxContentHeight = max($this->pdfDocument->GetY(), $maxContentHeight);
        ++$currentColumn;

        //filter used for generation
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
        $this->pdfDocument->SetY($this->getCurrentY());

        $this->printH3($filterHeader, $columnWidth);

        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        foreach ($filterEntries as $name => $value) {
            $this->pdfDocument->SetX($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
            $this->printHtmlP('<b>' . $name . '</b>: ' . $value);
        }

        //save content height for next part
        $this->setCurrentY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
    }

    private function printH2($text, $columnWidth = 0)
    {
        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getEmphasisFontFamily());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 2);
    }

    private function printH3($text, $columnWidth = 0)
    {
        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 1);
        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight());
    }

    private function printP($text, $columnWidth = 0)
    {
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 2);
    }

    private function printHtmlP($html)
    {
        $this->pdfDocument->writeHTMLCell(0, 0, $this->pdfDocument->GetX(), $this->pdfDocument->GetY(), $html, 0, 1);
        //-2 because the html does not stop at the correct height
        $this->pdfDocument->SetY($this->pdfDocument->GetY() - 2);
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
    public function addMap($map, $mapImageFilePath)
    {
        $this->setDefaults();

        if (file_exists($mapImageFilePath)) {
            list($width, $height) = ImageHelper::getWidthHeightArguments($mapImageFilePath, $this->pdfSizes->getContentXSize(), $this->pdfSizes->getContentYSize());
            $this->pdfDocument->Cell($this->pdfSizes->getContentXSize(), $height + $this->pdfSizes->getContentSpacerSmall() * 2, '', 1);
            $this->pdfDocument->SetY($this->getCurrentY() + $this->pdfSizes->getContentSpacerSmall());
            $this->pdfDocument->setPage($this->pdfDocument->getPage() - 1);
            $this->pdfDocument->Image($mapImageFilePath, $this->pdfSizes->getContentXStart() + (((float)$this->pdfSizes->getContentXSize() - $width)), $this->getCurrentY(), $width, $height, '', '', 'C', false, 300, '', '', false, 1);
        }
    }

    public function addTable($tableHeader, $tableContent, $tableName)
    {
        $this->setDefaults();

        $this->pdfDocument->SetXY($this->pdfSizes->getContentXStart(), $this->getCurrentY());

        //table header
        $this->printH2($tableName, $this->pdfSizes->getContentXSize());
        $this->setCurrentY($this->pdfDocument->GetY());

        //adapt font for table content
        $maxContentHeight = $this->getCurrentY();
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());

        //go through header
        $currentColumn = 0;
        $columnCount = count($tableHeader);
        $columnWidth = $this->pdfSizes->getColumnWidth($columnCount);
        foreach ($tableHeader as $item) {
            $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $this->getCurrentY());
            $this->pdfDocument->MultiCell($columnWidth, 0, mb_strtoupper($item), 0, 'L', true, 1);
            $maxContentHeight = max($maxContentHeight, $this->pdfDocument->GetY());
            ++$currentColumn;
        }
        $this->setCurrentY($maxContentHeight);

        $currentRow = 0;
        $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLighterBackground());
        foreach ($tableContent as $row) {
            //alternative background colors
            $fill = $currentRow % 2;

            //put columns
            $maxContentHeight = 0;
            $currentColumn = 0;
            foreach ($row as $item) {
                $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $this->getCurrentY());
                $this->pdfDocument->MultiCell($columnWidth, 0, $item, 0, 'L', $fill);
                $maxContentHeight = max($maxContentHeight, $this->pdfDocument->GetY());
                ++$currentColumn;
            }
            $this->setCurrentY($maxContentHeight);
            $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $this->getCurrentY(), $this->pdfSizes->getContentXEnd() + 2, $this->getCurrentY());
            ++$currentRow;
        }

        $this->setCurrentY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
    }
}
