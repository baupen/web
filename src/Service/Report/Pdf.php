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

use App\Helper\ImageHelper;
use Exception;
use TCPDF;

class Pdf extends TCPDF
{
    /**
     * @var PdfDefinition
     */
    private $pdfDefinition;

    /**
     * @var PdfSizes
     */
    private $pdfSizes;

    public function __construct(PdfDefinition $pdfDefinition, PdfSizes $pdfSizes)
    {
        parent::__construct();

        $this->pdfDefinition = $pdfDefinition;
        $this->pdfSizes = $pdfSizes;

        //set margin
        $this->SetMargins($this->pdfSizes->getContentXStart(), $this->pdfSizes->getContentYStart());
        $this->SetAutoPageBreak(true, $this->pdfSizes->getMarginBottom());

        // Set document meta-information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor($pdfDefinition->getAuthor());
        $this->SetTitle($pdfDefinition->getTitle());
    }

    /**
     * logo right & text left.
     */
    public function Header()
    {
        //image max one third, the header text the other two thirds
        $contentWidthPart = $this->pdfSizes->getContentXSize() / 3;

        //set image
        $headerHeight = $this->pdfSizes->getHeaderHeight();
        list($width, $height) = ImageHelper::fitInBoundingBox($this->pdfDefinition->getLogoPath(), $contentWidthPart, $headerHeight);
        $this->Image($this->pdfDefinition->getLogoPath(), $this->pdfSizes->getContentXEnd() - $width, $this->pdfSizes->getHeaderYStart(), $width, $height, '', '', 'R');

        //set header text
        $this->SetXY($this->pdfSizes->getContentXStart(), $this->pdfSizes->getHeaderYStart());
        $this->SetFontSize($this->pdfSizes->getRegularFontSize());
        $this->Cell($contentWidthPart * 2, 0, $this->pdfDefinition->getTitle(), 0, 0, 'L');
    }

    /**
     * bottom left author.
     */
    public function Footer()
    {
        //author three forths, page numbers one forth
        $contentWidthPart = $this->pdfSizes->getContentXSize() / 8;

        //set author
        $this->SetFontSize($this->pdfSizes->getSmallFontSize());
        $this->SetXY($this->pdfSizes->getContentXStart(), $this->pdfSizes->getFooterYStart());
        $this->Cell($contentWidthPart * 3, 0, $this->pdfDefinition->getAuthor(), 0, 0, 'L');

        //set page numbers
        //+10 because TCPDF uses a placeholder for the page numbers which is replaced at the end. this leads to incorrect alignment.
        $this->SetXY($this->pdfSizes->getContentXEnd() - $contentWidthPart + 6.5, $this->pdfSizes->getFooterYStart());
        $this->Cell($contentWidthPart, 0, $this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'R');
    }

    /**
     * @param $msg
     *
     * @throws Exception
     */
    public function Error($msg)
    {
        throw new Exception($msg);
    }
}
