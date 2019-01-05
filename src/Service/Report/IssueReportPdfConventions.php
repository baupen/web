<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Report\Pdf;

use App\Helper\ImageHelper;
use App\Service\Report\Pdf\Design\Interfaces\ColorServiceInterface;
use App\Service\Report\Pdf\Design\Interfaces\TypographyServiceInterface;
use App\Service\Report\Pdf\Design\TypographyService;
use App\Service\Report\Pdf\Interfaces\PdfDocumentInterface;
use App\Service\Report\Pdf\Interfaces\PdfPrinterInterface;

class IssueReportPdfConventions
{
    /**
     * @var TypographyService
     */
    private $typography;

    /**
     * @var ColorServiceInterface
     */
    private $color;

    /**
     * @var PdfPrinterInterface
     */
    private $pdfPrinter;

    /**
     * @param TypographyServiceInterface $typographyService
     * @param ColorServiceInterface $colorService
     */
    public function __construct(TypographyServiceInterface $typographyService, ColorServiceInterface $colorService)
    {
        // TODO: set pdf printer as an interface and inject it into layouts to close the circle
        $this->typography = $typographyService;
        $this->color = $colorService;
    }

    /**
     * @param string $paragraph
     * @param float $width
     */
    public function printParagraph(string $paragraph, float $width = null)
    {
        $this->printText($paragraph, $this->typography->getTextFontSize(), $width);
    }

    /**
     * @param string $title
     * @param float $width
     */
    public function printTitle(string $title, float $width = null)
    {
        $this->printBoldText($title, $this->typography->getTextFontSize(), $width);
    }

    /**
     * @param string $header
     * @param float $width
     */
    public function printRegionHeader(string $header, float $width = null)
    {
        $this->printBoldText($header, $this->typography->getTitleFontSize(), $width);
    }

    /**
     * @param string $filePath
     * @param float $width
     */
    public function printImage(string $filePath, float $width = null)
    {
        $this->pdfPrinter->registerPrintable(function ($document) use ($filePath, $width) {
            /* @var PdfDocumentInterface $document */
            list($width, $height) = ImageHelper::getWidthHeightArguments($filePath, $width);
            $document->printImage($filePath, $width, $height);
        });
    }

    /**
     * @param string[] $keyValues
     * @param float|null $width
     */
    public function printKeyValueParagraph(array $keyValues, float $width = null)
    {
        foreach ($keyValues as $key => $value) {
            $this->printBoldText($key, $this->typography->getTextFontSize(), $width);
            $this->printText($value, $this->typography->getTextFontSize(), $width);
        }
    }

    /**
     * @param string $imagePath
     * @param int $number
     * @param float $defaultWidth
     */
    public function printIssueImage(string $imagePath, int $number, float $defaultWidth)
    {
        $this->pdfPrinter->registerPrintable(function ($document) use ($imagePath, $number, $defaultWidth) {
            /* @var PdfDocumentInterface $document */
            list($width, $height) = ImageHelper::getWidthHeightArguments($imagePath, $defaultWidth);
            $document->printImage($imagePath, $width, $height);
            $afterImageCursor = $document->getCursor();

            // put cursor to top left corner of image
            $document->setCursor($afterImageCursor->setY($afterImageCursor->getYCoordinate() - $height));

            // print number of issue
            $document->configurePrint(['background' => $this->color->getImageOverlayColor()]);
            $document->printText((string)$number, $this->typography->getTextFontSize());

            // reset cursor to after image
            $document->setCursor(...$afterImageCursor);
        });
    }

    /**
     * @param string $text
     * @param float $fontSize
     * @param float $width
     */
    private function printText(string $text, float $fontSize, float $width)
    {
        $this->pdfPrinter->registerPrintable(function ($document) use ($text, $fontSize, $width) {
            /* @var PdfDocumentInterface $document */
            $document->configurePrint(['fontSize' => $fontSize]);
            $document->printText($text, $width);
        });
    }

    /**
     * @param string $text
     * @param float $fontSize
     * @param float $width
     */
    private function printBoldText(string $text, float $fontSize, float $width)
    {
        $this->pdfPrinter->registerPrintable(function ($document) use ($text, $fontSize, $width) {
            /* @var PdfDocumentInterface $document */
            $document->configurePrint(['fontSize' => $fontSize, 'bold' => true]);
            $document->printText($text, $width);
        });
    }
}
