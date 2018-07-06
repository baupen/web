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
            list($width, $height) = $this->pdfSizes->getWidthHeightArguments($imagePath, $maxImageWidth, $maxImageWidth);
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

        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->MultiCell($columnWidth, 0, $constructionSite->getName(), 0, 'L', false, 1);
        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->MultiCell($columnWidth, 0, implode("\n", $constructionSite->getAddressLines()), 0, 'L', false, 2);
        $maxContentHeight = max($this->pdfDocument->GetY(), $maxContentHeight);
        ++$currentColumn;

        //filter used for generation
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
        $this->pdfDocument->SetY($this->getCurrentY());

        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->MultiCell($columnWidth, 0, $filterHeader, 0, 'L', false, 1);
        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        foreach ($filterEntries as $name => $value) {
            $this->pdfDocument->writeHTML('<b>' . $name . '</b>: ' . $value, true, false, false, true);
        }

        //save content height for next part
        $this->setCurrentY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
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
        $this->setDefaults();
    }

    public function addTable($tableHeader, $tableContent, $tableName)
    {
        $this->setDefaults();

        $this->pdfDocument->SetXY($this->pdfSizes->getContentXStart(), $this->getCurrentY());

        //table header
        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getEmphasisFontFamily());
        $this->pdfDocument->MultiCell($this->pdfSizes->getContentXSize(), 0, $tableName, 0, 'L', false, 2);
        $this->setCurrentY($this->pdfDocument->GetY() + $this->pdfSizes->getContentSpacerSmall());

        //adapt font for table content
        $maxContentHeight = $this->getCurrentY();
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());

        //go through header
        $currentColumn = 0;
        $columnCount = count($tableHeader);
        $columnContentWidth = $this->pdfSizes->getColumnContentWidth($columnCount);
        foreach ($tableHeader as $item) {
            $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $this->getCurrentY());
            $this->pdfDocument->MultiCell($columnContentWidth, 0, mb_strtoupper($item), 0, 'L', true, 1);
            $maxContentHeight = max($maxContentHeight, $this->pdfDocument->GetY());
            ++$currentColumn;
        }
        $this->setCurrentY($maxContentHeight);

        $currentRow = 0;
        foreach ($tableContent as $row) {
            //alternative background colors
            if ($currentRow % 2 === 0) {
                $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLightBackground());
            } else {
                $this->pdfDocument->SetFillColor(...$this->pdfDesign->getLighterBackground());
            }

            //put columns
            $maxContentHeight = 0;
            $currentColumn = 0;
            foreach ($row as $item) {
                $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $this->getCurrentY());
                $this->pdfDocument->MultiCell($columnContentWidth, 0, $item, 0, 'L');
                $maxContentHeight = max($maxContentHeight, $this->pdfDocument->GetY());
                ++$currentColumn;
            }
            $this->setCurrentY($maxContentHeight);
            $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $this->getCurrentY(), $this->pdfSizes->getContentXEnd(), $this->getCurrentY());
            ++$currentRow;
        }

        $this->setCurrentY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
    }
}
