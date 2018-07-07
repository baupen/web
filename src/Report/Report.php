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

        //set position
        $this->pdfDocument->SetXY($this->pdfSizes->getContentXStart(), $this->getCurrentY());
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

        $this->printH2($constructionSite->getName(), $columnWidth);

        $this->printP(implode("\n", $constructionSite->getAddressLines()), $columnWidth);
        $maxContentHeight = max($this->pdfDocument->GetY(), $maxContentHeight);
        ++$currentColumn;

        //filter used for generation
        $this->pdfDocument->SetLeftMargin($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
        $this->pdfDocument->SetY($this->getCurrentY());

        $this->printH2($filterHeader, $columnWidth);

        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        foreach ($filterEntries as $name => $value) {
            $this->pdfDocument->SetX($this->pdfSizes->getColumnStart($currentColumn, $columnCount));
            $this->printHtmlP('<b>' . $name . '</b>: ' . $value);
        }

        //save content height for next part
        $this->setCurrentY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
    }

    private function printH2($text, $columnWidth = 0, $description = '')
    {
        $this->pdfDocument->SetFontSize($this->pdfSizes->getBigFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 1);

        if (mb_strlen($description) > 0) {
            $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
            $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
            $this->pdfDocument->MultiCell($columnWidth, 0, $description, 0, 'L', false, 1);
        }

        $this->pdfDocument->Ln($this->pdfSizes->getLnHeight());
    }

    private function printH3($text, $columnWidth = 0, $description = '')
    {
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getEmphasisFontFamily());
        $this->pdfDocument->MultiCell($columnWidth, 0, $text, 0, 'L', false, 1);

        if (mb_strlen($description) > 0) {
            $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
            $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());
            $this->pdfDocument->MultiCell($columnWidth, 0, $description, 0, 'L', false, 1);
        }
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
        $this->pdfDocument->SetY($this->pdfDocument->GetY());
    }

    private function getHeightOf($closure)
    {
        $this->pdfDocument->startTransaction();
        if ($this->pdfDocument->GetY() > $this->pdfSizes->getPageSizeY()) {
            $this->pdfDocument->AddPage();
        }
        $startY = $this->pdfDocument->GetY();
        $startPage = $this->pdfDocument->getPage();
        $closure();
        $endY = $this->pdfDocument->GetY();
        $endPage = $this->pdfDocument->getPage();
        $this->pdfDocument = $this->pdfDocument->rollbackTransaction();
        $pageAdapt = $this->pdfSizes->getContentYSize() * ($endPage - $startPage);

        return $endY - $startY + $pageAdapt;
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
     * @param string $mapImageFilePath
     */
    public function addMap($map, $mapImageFilePath)
    {
        $this->setDefaults();

        $headerHeight = $this->getHeightOf(function () use ($map) {
            $this->printH2($map->getName(), 0, $map->getContext());
        });

        if (file_exists($mapImageFilePath)) {
            $imgPadding = 2 * $this->pdfSizes->getImagePadding();
            $maxWidth = $this->pdfSizes->getContentXSize() - $imgPadding;
            $maxHeight = $this->pdfSizes->getContentYSize() - $headerHeight - $imgPadding;
            list($width, $height) = ImageHelper::getWidthHeightArguments($mapImageFilePath, $maxWidth, $maxHeight);

            if ($headerHeight + $height + $this->getCurrentY() + $this->pdfSizes->getContentSpacerBig() + $imgPadding < $this->pdfSizes->getContentYEnd()) {
                //add content spacer & continue on same page
                $this->pdfDocument->SetY($this->getCurrentY() + $this->pdfSizes->getContentSpacerSmall(), true, false);
            } else {
                //force new page
                $this->pdfDocument->AddPage();
            }
            $this->printH2($map->getName(), 0, $map->getContext());
            $this->setCurrentY($this->pdfDocument->GetY());
            $this->pdfDocument->Cell($this->pdfSizes->getContentXSize(), $height + $imgPadding, '', 0, 1, '', true);
            $this->setCurrentY($this->getCurrentY() + $this->pdfSizes->getImagePadding());
            $this->pdfDocument->Image($mapImageFilePath, $this->pdfSizes->getContentXStart() + (((float)$this->pdfSizes->getContentXSize() - $width) / 2), $this->getCurrentY(), $width, $height);
            $this->setCurrentY($this->pdfDocument->GetY() + $height);
        }
    }

    public function addTable($tableHead, $tableContent, $tableTitle = null)
    {
        $this->setDefaults();

        if ($tableTitle !== null) {
            //table header
            $this->printH3($tableTitle, $this->pdfSizes->getContentXSize());
            $this->setCurrentY($this->pdfDocument->GetY());
        }

        //adapt font for table content
        $maxContentHeight = $this->getCurrentY();
        $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());
        $this->pdfDocument->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->pdfDocument->SetFont(...$this->pdfDesign->getDefaultFontFamily());

        //go through header
        $currentColumn = 0;
        $columnCount = count($tableHead);
        foreach ($tableHead as $item) {
            $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $this->getCurrentY());
            $this->pdfDocument->MultiCell($this->pdfSizes->getColumnWidth($currentColumn, $columnCount), 0, mb_strtoupper($item), 0, 'L', true, 1);
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
            $fullWidth = 0;
            foreach ($row as $item) {
                $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart($currentColumn, $columnCount), $this->getCurrentY());
                $currentWidth = $this->pdfSizes->getColumnWidth($currentColumn, $columnCount);

                $this->pdfDocument->MultiCell($currentWidth, $maxContentHeight - $this->getCurrentY(), $item, 0, 'L', $fill, 1);

                if ($maxContentHeight !== 0 && $this->pdfDocument->GetY() > $maxContentHeight) {
                    $diff = $this->pdfDocument->GetY() - $maxContentHeight;
                    $newMaxHeight = $this->pdfDocument->GetY();
                    $this->pdfDocument->SetXY($this->pdfSizes->getColumnStart(0, $columnCount), $maxContentHeight);
                    $this->pdfDocument->SetCellPadding(0);
                    $this->pdfDocument->Cell($fullWidth, $diff - 2, '', 0, 0, '', $fill);
                    $this->pdfDocument->setCellPaddings(...$this->pdfSizes->getTableCellPadding());

                    $this->pdfDocument->SetY($newMaxHeight);
                    $maxContentHeight += $diff;
                } else {
                    $maxContentHeight = $this->pdfDocument->GetY();
                }
                ++$currentColumn;
                $fullWidth += $currentWidth;
            }
            $this->setCurrentY($maxContentHeight);
            $this->pdfDocument->Line($this->pdfSizes->getContentXStart(), $this->getCurrentY(), $this->pdfSizes->getContentXEnd(), $this->getCurrentY());
            ++$currentRow;
        }

        $this->setCurrentY(max($this->pdfDocument->GetY(), $maxContentHeight) + $this->pdfSizes->getContentSpacerBig());
    }
}
