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

use TCPDF;

class Pdf extends TCPDF
{
    /**
     * @var PdfDefinition
     */
    private $pdfDefinition;

    public function __construct(PdfDefinition $pdfDefinition)
    {
        parent::__construct();

        $this->pdfDefinition = $pdfDefinition;

        $this->SetMargins(72, 36, 72, true);
        $this->SetAutoPageBreak(true, 36);

        // Set document meta-information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor($pdfDefinition->getAuthor());
        $this->SetTitle($pdfDefinition->getTitle());

        //set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    // Page header and footer code.
    public function Header()
    {
        // The image is this much larger than the company name text.
        $this->ImagePngAlpha($this->pdfDefinition->getLogoPath(), 19, 19, 100, 40, 100, 20, 'PNG', null, 'T', false, 72, 'L');

        /*
                $this->SetY(1.5 * 72, true);
                $this->SetLineStyle(['width' => 2, 'color' => [$webcolor['black']]]);
                $this->Line(72, 36 + $imageScale, $this->getPageWidth() - 72, 36 + $imageScale);
                */
    }

    public function Footer()
    {
        $this->SetFont('times', '', 8);
        $this->SetY(-1.5 * 72, true);
        $this->Cell(72, 0, 'sample');
    }

    private function CreateInvoice()
    {
        $this->AddPage();
        $this->SetFont('helvetica', '', 11);
        $this->SetY(144, true);

        // Table parameters
        //
        // Column size, wide (description) column, table indent, row height.
        $col = 72;
        $wideCol = 3 * $col;
        $indent = ($this->getPageWidth() - 2 * 72 - $wideCol - 3 * $col) / 2;
        $line = 18;

        // Table header
        $this->SetFont('', 'b');
        $this->Cell($indent);
        $this->Cell($wideCol, $line, 'Item', 1, 0, 'L');
        $this->Cell($col, $line, 'Quantity', 1, 0, 'R');
        $this->Cell($col, $line, 'Price', 1, 0, 'R');
        $this->Cell($col, $line, 'Cost', 1, 0, 'R');
        $this->Ln();

        // Table content rows
        $this->SetFont('', '');
        foreach ($this->invoiceData['items'] as $item) {
            $this->Cell($indent);
            $this->Cell($wideCol, $line, $item[0], 1, 0, 'L');
            $this->Cell($col, $line, $item[1], 1, 0, 'R');
            $this->Cell($col, $line, $item[2], 1, 0, 'R');
            $this->Cell($col, $line, $item[3], 1, 0, 'R');
            $this->Ln();
        }

        // Table Total row
        $this->SetFont('', 'b');
        $this->Cell($indent);
        $this->Cell($wideCol + $col * 2, $line, 'Total:', 1, 0, 'R');
        $this->SetFont('', '');
        $this->Cell($col, $line, $this->invoiceData['total'], 1, 0, 'R');
    }
}
