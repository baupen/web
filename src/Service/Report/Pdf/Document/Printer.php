<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf\Document;

use App\Helper\ImageHelper;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Design\TypographyService;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;

class Printer
{
    /**
     * @var PdfDocumentInterface
     */
    private $document;

    /**
     * @var TypographyService
     */
    private $typography;

    /**
     * @param PdfDocumentInterface $pdfDocument
     * @param TypographyServiceInterface $typographyService
     */
    public function __construct(PdfDocumentInterface $pdfDocument, TypographyServiceInterface $typographyService)
    {
        $this->document = $pdfDocument;
        $this->typography = $typographyService;
    }

    /**
     * @param string $paragraph
     * @param float $width
     */
    public function printParagraph(string $paragraph, float $width = null)
    {
        $this->document->printText($paragraph, $this->typography->getTextFontSize(), $width);
    }

    /**
     * @param string $title
     * @param float $width
     */
    public function printTitle(string $title, float $width = null)
    {
        $this->document->printBoldText($title, $this->typography->getTextFontSize(), $width);
    }

    /**
     * @param string $header
     * @param float $width
     */
    public function printRegionHeader(string $header, float $width = null)
    {
        $this->document->printBoldText($header, $this->typography->getTitleFontSize(), $width);
    }

    /**
     * @param string[] $header
     * @param string[][] $content
     * @param float|null $width
     */
    public function printTable(array $header, array $content, float $width = null)
    {
        $this->document->printTable($header, $content, $width);
    }

    /**
     * @param string $filePath
     * @param float $width
     */
    public function printImage(string $filePath, float $width = null)
    {
        $cursor = $this->document->getCursor();

        list($width, $height) = ImageHelper::getWidthHeightArguments($filePath, $width);
        $this->document->printImage($filePath, $width, $height);

        // put cursor below the image
        $this->document->setCursor($cursor[0], $cursor[1] + $height);
    }

    /**
     * @param string[] $keyValues
     * @param float|null $width
     */
    public function printKeyValueParagraph(array $keyValues, float $width = null)
    {
        foreach ($keyValues as $key => $value) {
            $this->document->printBoldText($key, $this->typography->getTextFontSize(), $width);
            $this->document->printText($value, $this->typography->getTextFontSize(), $width);
        }
    }
}
